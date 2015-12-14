<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
use App\Console\Application;
use Infuse\Config;
use Infuse\ErrorStack;
use Infuse\Locale;
use Infuse\Model;
use Infuse\Response;
use Infuse\Request;
use Infuse\Router;
use Infuse\Utility as U;
use Infuse\Validate;
use Infuse\View;
use Infuse\Queue;
use JAQB\QueryBuilder;
use Monolog\ErrorHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Logger;
use Pimple\Container;

if (!defined('INFUSE_BASE_DIR')) {
    die('INFUSE_BASE_DIR has not been defined!');
}

/* app constants */
if (!defined('INFUSE_APP_DIR')) {
    define('INFUSE_APP_DIR', INFUSE_BASE_DIR.'/app');
}
if (!defined('INFUSE_ASSETS_DIR')) {
    define('INFUSE_ASSETS_DIR', INFUSE_BASE_DIR.'/assets');
}
if (!defined('INFUSE_PUBLIC_DIR')) {
    define('INFUSE_PUBLIC_DIR', INFUSE_BASE_DIR.'/public');
}
if (!defined('INFUSE_TEMP_DIR')) {
    define('INFUSE_TEMP_DIR', INFUSE_BASE_DIR.'/temp');
}
if (!defined('INFUSE_VIEWS_DIR')) {
    define('INFUSE_VIEWS_DIR', INFUSE_BASE_DIR.'/views');
}

class App extends Container
{
    public function __construct(array $configValues = [])
    {
        parent::__construct();

        $app = $this;

        /* Load Configuration */

        $this['config'] = function () use ($configValues) {
            return new Config($configValues);
        };

        $config = $app['config'];

        /* Error Reporting */

        ini_set('display_errors', !$config->get('site.production-level'));
        ini_set('log_errors', 1);
        error_reporting(E_ALL | E_STRICT);

        /* Logger */

        $this['logger'] = function () use ($app, $config) {
            $handlers = [];

            if ($config->get('logger.enabled')) {
                $webProcessor = new WebProcessor();
                $webProcessor->addExtraField('user_agent', 'HTTP_USER_AGENT');

                $processors = [
                    $webProcessor,
                    new IntrospectionProcessor(), ];

                // firephp
                if (!$config->get('site.production-level')) {
                    $handlers[] = new FirePHPHandler();
                }
            } else {
                $processors = [];
                $handlers[] = new NullHandler();
            }

            return new Logger($config->get('site.hostname'), $handlers, $processors);
        };

        ErrorHandler::register($this['logger']);

        /* Time Zone */

        if ($tz = $config->get('site.time-zone')) {
            date_default_timezone_set($tz);
        }

        /* Locale */

        $this['locale'] = function () use ($app, $config) {
            $locale = new Locale($config->get('site.language'));
            $locale->setLocaleDataDir(INFUSE_ASSETS_DIR.'/locales');

            return $locale;
        };

        /* Validator */

        Validate::configure(['salt' => $config->get('site.salt')]);

        /* Database */

        $dbSettings = (array) $config->get('database');

        $this['pdo'] = function () use ($dbSettings, $config, $app) {
            if (isset($dbSettings['dsn'])) {
                $dsn = $dbSettings['dsn'];
            } else { // generate the dsn
                $dsn = $dbSettings['type'].':host='.$dbSettings['host'].';dbname='.$dbSettings['name'];
            }

            $user = U::array_value($dbSettings, 'user');
            $password = U::array_value($dbSettings, 'password');

            try {
                $pdo = new PDO($dsn, $user, $password);
            } catch (\Exception $e) {
                $app['logger']->emergency($e);
                die('Could not connect to database.');
            }

            if ($config->get('site.production-level')) {
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            } else {
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }

            return $pdo;
        };

        $this['db'] = function () use ($app) {
            return new QueryBuilder($app['pdo']);
        };

        /* Redis */

        $redisConfig = $config->get('redis');
        if ($redisConfig) {
            $this['redis'] = function () use ($redisConfig) {
                $redis = new Redis();
                $redis->connect($redisConfig['host'], $redisConfig['port']);

                return $redis;
            };
        }

        /* Memcache */

        $memcacheConfig = $config->get('memcache');
        if ($memcacheConfig) {
            $this['memcache'] = function () use ($memcacheConfig) {
                $memcache = new Memcache();
                $memcache->connect($memcacheConfig['host'], $memcacheConfig['port']);

                return $memcache;
            };
        }

        /* Queue */

        $class = $config->get('queue.driver');
        if ($class) {
            Queue::setDriver(new $class($this));
        }

        /* Models */

        $class = $config->get('models.driver');
        if ($class) {
            Model::inject($this);
            Model::setDriver(new $class($this));
        }

        /* Error Stack */

        $this['errors'] = function () use ($app) {
            return new ErrorStack($app);
        };

        /* View Engine  */

        $this['view_engine'] = function () use ($app, $config) {
            $class = $config->get('views.engine');

            // use PHP view engine by default
            if (!$class) {
                $class = 'Infuse\ViewEngine\PHP';
            }

            // Smarty needs special parameters
            if ($class === 'Infuse\ViewEngine\Smarty') {
                $engine = new $class(INFUSE_VIEWS_DIR, INFUSE_TEMP_DIR.'/smarty', INFUSE_TEMP_DIR.'/smarty/cache');
            } else {
                $engine = new $class(INFUSE_VIEWS_DIR);
            }

            // static assets
            $engine->setAssetMapFile(INFUSE_ASSETS_DIR.'/static.assets.json')
                   ->setAssetBaseUrl($config->get('assets.base_url'))
                   ->setGlobalParameters(['app' => $app]);

            return $engine;
        };

        View::inject($this);

        $config->set('assets.dirs', [INFUSE_PUBLIC_DIR]);

        /* Router */

        $this['router'] = function () use ($config) {
            $routes = (array) $this['config']->get('routes');
            $settings = (array) $config->get('router');

            return new Router($routes, $settings);
        };

        /* Base URL */

        $this['base_url'] = function () use ($config) {
            $url = (($config->get('site.ssl')) ? 'https' : 'http').'://';
            $url .= $config->get('site.hostname');
            $port = $config->get('site.port');
            $url .= ((!in_array($port, [0, 80, 443])) ? ':'.$port : '').'/';

            return $url;
        };
    }

    ////////////////////////
    // ROUTING
    ////////////////////////

    /**
     * Adds a handler to the routing table for a given GET route.
     *
     * @param string $route   path pattern
     * @param mixed  $handler route handler
     *
     * @return self
     */
    public function get($route, $handler)
    {
        $this['router']->get($route, $handler);

        return $this;
    }

    /**
     * Adds a handler to the routing table for a given POST route.
     *
     * @param string $route   path pattern
     * @param mixed  $handler route handler
     *
     * @return self
     */
    public function post($route, $handler)
    {
        $this['router']->post($route, $handler);

        return $this;
    }

    /**
     * Adds a handler to the routing table for a given PUT route.
     *
     * @param string $route   path pattern
     * @param mixed  $handler route handler
     *
     * @return self
     */
    public function put($route, $handler)
    {
        $this['router']->put($route, $handler);

        return $this;
    }

    /**
     * Adds a handler to the routing table for a given DELETE route.
     *
     * @param string $route   path pattern
     * @param mixed  $handler route handler
     *
     * @return self
     */
    public function delete($route, $handler)
    {
        $this['router']->delete($route, $handler);

        return $this;
    }

    /**
     * Adds a handler to the routing table for a given PATCH route.
     *
     * @param string $route   path pattern
     * @param mixed  $handler route handler
     *
     * @return self
     */
    public function patch($route, $handler)
    {
        $this['router']->patch($route, $handler);

        return $this;
    }

    /**
     * Adds a handler to the routing table for a given OPTIONS route.
     *
     * @param string $route   path pattern
     * @param mixed  $handler route handler
     *
     * @return self
     */
    public function options($route, $handler)
    {
        $this['router']->options($route, $handler);

        return $this;
    }

    /**
     * Adds a handler to the routing table for a given route.
     *
     * @param string $method  HTTP method
     * @param string $route   path pattern
     * @param mixed  $handler route handler
     *
     * @return self
     */
    public function map($method, $route, $handler)
    {
        $this['router']->map($method, $route, $handler);

        return $this;
    }

    ////////////////////////
    // REQUESTS
    ////////////////////////

    /**
     * Runs the app.
     *
     * @return self
     */
    public function run()
    {
        $req = Request::createFromGlobals();

        $this->handleRequest($req)->send();

        return $this;
    }

    /**
     * @deprecated
     */
    public function go()
    {
        return $this->run();
    }

    /**
     * Builds a response to an incoming request by routing
     * it through the app.
     *
     * @param Request $req
     *
     * @return Response
     */
    public function handleRequest(Request $req)
    {
        // set host name from request if not already set
        $config = $this['config'];
        if (!$config->get('site.hostname')) {
            $config->set('site.hostname', $req->host());
        }

        // start a new session
        $this->startSession($req);

        // create a blank response
        $res = new Response();

        // middleware
        $this->executeMiddleware($req, $res);

        // route the request
        $routed = $this['router']->route($this, $req, $res);

        // HTML Error Pages for 4xx and 5xx responses
        $code = $res->getCode();
        if ($req->isHtml() && $code >= 400) {
            $body = $res->getBody();
            if (empty($body)) {
                $res->render(new View('error', [
                    'message' => Response::$codes[$code],
                    'code' => $code,
                    'title' => $code, ]));
            }
        }

        return $res;
    }

    /**
     * Starts a session.
     *
     * @param Request $req
     *
     * @return self
     */
    public function startSession(Request $req)
    {
        $config = $this['config'];
        if (!$config->get('sessions.enabled') || $req->isApi()) {
            return $this;
        }

        $lifetime = $config->get('sessions.lifetime');
        $hostname = $config->get('site.hostname');
        ini_set('session.use_trans_sid', false);
        ini_set('session.use_only_cookies', true);
        ini_set('url_rewriter.tags', '');
        ini_set('session.gc_maxlifetime', $lifetime);

        // set the session name
        $sessionTitle = $config->get('site.title').'-'.$hostname;
        $safeSessionTitle = str_replace(['.', ' ', "'", '"'], ['', '_', '', ''], $sessionTitle);
        session_name($safeSessionTitle);

        // set the session cookie parameters
        session_set_cookie_params(
            $lifetime, // lifetime
            '/', // path
            '.'.$hostname, // domain
            $req->isSecure(), // secure
            true // http only
        );

        // install any custom session handlers
        $class = $config->get('sessions.driver');
        if ($class) {
            $handler = new $class($this, $config->get('sessions.prefix'));
            $handler::registerHandler($handler);
        }

        session_start();

        // fix the session cookie
        U::set_cookie_fix_domain(
            session_name(),
            session_id(),
            time() + $lifetime,
            '/',
            $hostname,
            $req->isSecure(),
            true
        );

        // make the newly started session in our request
        $req->setSession($_SESSION);

        return $this;
    }

    /**
     * Gets the middleware modules.
     *
     * @return array
     */
    public function getMiddleware()
    {
        return (array) $this['config']->get('modules.middleware');
    }

    /**
     * Executes the app's middleware.
     *
     * @param Request  $req
     * @param Response $res
     *
     * @return self
     */
    public function executeMiddleware(Request $req, Response $res)
    {
        foreach ($this->getMiddleware() as $module) {
            $class = 'App\\'.$module.'\Controller';
            $controller = new $class();
            if (method_exists($controller, 'injectApp')) {
                $controller->injectApp($this);
            }
            $controller->middleware($req, $res);
        }

        return $this;
    }

    ////////////////////////
    // CONSOLE
    ////////////////////////

    /**
     * Gets a console application instance for this app.
     *
     * @return \App\Console\Application
     */
    public function getConsole()
    {
        return new Application($this);
    }
}
