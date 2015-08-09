<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
use infuse\Config;
use infuse\ErrorStack;
use infuse\Locale;
use infuse\Model;
use infuse\Response;
use infuse\Request;
use infuse\Router;
use infuse\Utility as U;
use infuse\Validate;
use infuse\ViewEngine;
use infuse\View;
use infuse\Queue;
use infuse\Session\Redis as RedisSession;
use JAQB\Session as DatabaseSession;
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

/* error codes */
if (!defined('ERROR_NO_PERMISSION')) {
    define('ERROR_NO_PERMISSION', 'no_permission');
}
if (!defined('VALIDATION_FAILED')) {
    define('VALIDATION_FAILED', 'validation_failed');
}
if (!defined('VALIDATION_REQUIRED_FIELD_MISSING')) {
    define('VALIDATION_REQUIRED_FIELD_MISSING', 'required_field_missing');
}
if (!defined('VALIDATION_NOT_UNIQUE')) {
    define('VALIDATION_NOT_UNIQUE', 'not_unique');
}

/* useful constants */
if (!defined('SKIP_ROUTE')) {
    define('SKIP_ROUTE', -1);
}

class App extends Container
{
    /**
     * @var array
     */
    private $routes;

    public function __construct(array $configValues = [])
    {
        parent::__construct();

        $app = $this;

        /* Load Configuration */

        $this['config'] = function () use ($configValues) {
            return new Config($configValues);
        };

        $config = $app['config'];

        /* Logging */

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

        /* Error Reporting */

        ini_set('display_errors', !$config->get('site.production-level'));
        ini_set('log_errors', 1);
        error_reporting(E_ALL | E_STRICT);

        ErrorHandler::register($this['logger']);

        /* Time Zone */

        if ($tz = $config->get('site.time-zone')) {
            date_default_timezone_set($tz);
        }

        /* Constants */

        if (!defined('SITE_TITLE')) {
            define('SITE_TITLE', $config->get('site.title'));
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
                return new Predis\Client($redisConfig);
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

        /* Request + Response */

        $this['req'] = function () {
            return new Request();
        };

        $this['res'] = function () {
            return new Response();
        };

        $req = $this[ 'req' ];
        $res = $this[ 'res' ];

        // set host name if one is not provided
        if (!$config->get('site.hostname')) {
            $config->set('site.hostname', $req->host());
        }

        $port = $config->get('site.port');
        $this['base_url'] = (($config->get('site.ssl')) ? 'https' : 'http').'://'.
            $config->get('site.hostname').((!in_array($port, [0, 80, 443])) ? ':'.$port : '').'/';

        /* Queue */

        $this['queue'] = function () use ($app, $config) {
            Queue::configure(array_merge(['namespace' => '\\app'],
                (array) $config->get('queue')));

            Queue::inject($app);

            return new Queue($config->get('queue.type'),
                (array) $config->get('queue.listeners'));
        };

        /* Models */

        Model::inject($this);
        Model::configure((array) $config->get('models'));

        /* Error Stack */

        $this['errors'] = function () use ($app) {
            return new ErrorStack($app);
        };

        /* Views  */

        $this['view_engine'] = function () use ($app, $config) {
            $type = $config->get('views.engine');
            if ($type == 'smarty') {
                $engine = new ViewEngine\Smarty(INFUSE_VIEWS_DIR, INFUSE_TEMP_DIR.'/smarty', INFUSE_TEMP_DIR.'/smarty/cache');
            } elseif ($type == 'php' || !$type) {
                $engine = new ViewEngine\PHP(INFUSE_VIEWS_DIR);
            } else {
                throw new Exception("'$type' is not a valid view engine");
            }

            $engine->setAssetMapFile(INFUSE_ASSETS_DIR.'/static.assets.json')
                   ->setAssetBaseUrl($config->get('assets.base_url'))
                   ->setGlobalParameters(['app' => $app]);

            return $engine;
        };

        View::inject($this);

        $config->set('assets.dirs', [ INFUSE_PUBLIC_DIR ]);

        /* Session */

        if (!$req->isApi() && $config->get('sessions.enabled')) {
            // initialize sessions
            ini_set('session.use_trans_sid', false);
            ini_set('session.use_only_cookies', true);
            ini_set('url_rewriter.tags', '');
            ini_set('session.gc_maxlifetime', $config->get('sessions.lifetime'));

            $hostname = $config->get('site.hostname');

            // set the session name
            $sessionTitle = $config->get('site.title').'-'.$hostname;
            $safeSessionTitle = str_replace([ '.', ' ', "'", '"' ], [ '', '_', '', '' ], $sessionTitle);
            session_name($safeSessionTitle);

            // set the session cookie parameters
            session_set_cookie_params(
                $config->get('sessions.lifetime'), // lifetime
                '/', // path
                '.'.$hostname, // domain
                $req->isSecure(), // secure
                true // http only
            );

            $sessionAdapter = $config->get('sessions.adapter');
            if ($sessionAdapter == 'redis') {
                $handler = new RedisSession($app, $config->get('sessions.prefix'));
                RedisSession::registerHandler($handler);
                session_start();
            } elseif ($sessionAdapter == 'database') {
                $handler = new DatabaseSession($app);
                DatabaseSession::registerHandler($handler);
                session_start();
            } elseif (empty($sessionAdapter) || $sessionAdapter == 'php') {
                session_start();
            }

            // set the cookie by sending it in a header.
            U::set_cookie_fix_domain(
                session_name(),
                session_id(),
                time() + $config->get('sessions.lifetime'),
                '/',
                $hostname,
                $req->isSecure(),
                true
            );

            // update the session in our request
            $req->setSession($_SESSION);
        }

        /* CLI Requests */

        if ($req->isCli()) {
            global $argc, $argv;
            if ($argc >= 2) {
                $req->setPath($argv[1]);
            }
        }

        /* Router */

        Router::configure(['namespace' => '\\app']);

        $routes = (array) $this['config']->get('routes');

        /* Middleware */

        foreach ((array) $config->get('modules.middleware') as $module) {
            $class = '\\app\\'.$module.'\\Controller';
            $controller = new $class();
            if (method_exists($controller, 'injectApp')) {
                $controller->injectApp($app);
            }
            $controller->middleware($req, $res);
        }
    }

    ////////////////////////
    // ROUTING
    ////////////////////////

    public function go()
    {
        $routed = false;

        $req = $this['req'];
        $res = $this['res'];

        /* 1. Global Routes */
        $routed = Router::route($this->routes, $this, $req, $res);

        /* 2. Not Found */
        if (!$routed) {
            $res->setCode(404);
        }

        /* 3. HTML Error Pages for 4xx and 5xx responses */
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

        $res->send();
    }

    /**
     * Adds a handler to the routing table for a given GET route.
     *
     * @param string   $route   path pattern
     * @param callable $handler route handler
     *
     * @return self
     */
    public function get($route, callable $handler)
    {
        $this->map('get', $route, $handler);

        return $this;
    }

    /**
     * Adds a handler to the routing table for a given POST route.
     *
     * @param string   $route   path pattern
     * @param callable $handler route handler
     *
     * @return self
     */
    public function post($route, callable $handler)
    {
        $this->map('post', $route, $handler);

        return $this;
    }

    /**
     * Adds a handler to the routing table for a given PUT route.
     *
     * @param string   $route   path pattern
     * @param callable $handler route handler
     *
     * @return self
     */
    public function put($route, callable $handler)
    {
        $this->map('put', $route, $handler);

        return $this;
    }

    /**
     * Adds a handler to the routing table for a given DELETE route.
     *
     * @param string   $route   path pattern
     * @param callable $handler route handler
     *
     * @return self
     */
    public function delete($route, callable $handler)
    {
        $this->map('delete', $route, $handler);

        return $this;
    }

    /**
     * Adds a handler to the routing table for a given PATCH route.
     *
     * @param string   $route   path pattern
     * @param callable $handler route handler
     *
     * @return self
     */
    public function patch($route, callable $handler)
    {
        $this->map('patch', $route, $handler);

        return $this;
    }

    /**
     * Adds a handler to the routing table for a given OPTIONS route.
     *
     * @param string   $route   path pattern
     * @param callable $handler route handler
     *
     * @return self
     */
    public function options($route, callable $handler)
    {
        $this->map('options', $route, $handler);

        return $this;
    }

    /**
     * Adds a handler to the routing table for a given route.
     *
     * @param string   $method  HTTP method
     * @param string   $route   path pattern
     * @param callable $handler route handler
     *
     * @return self
     */
    public function map($method, $route, callable $handler)
    {
        $method = strtolower($method);
        $this->routes[$method.' '.$route] = $handler;

        return $this;
    }

    /**
     * Gets the routing table.
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
