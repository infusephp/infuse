<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
namespace Infuse;

use FastRoute\Dispatcher;
use Pimple\Container;

class Application extends Container
{
    const ENV_PRODUCTION = 'production';
    const ENV_DEVELOPMENT = 'development';
    const ENV_TEST = 'test';

    /**
     * @staticvar array
     */
    protected static $baseConfig = [
        'app' => [
            'environment' => self::ENV_DEVELOPMENT,
            'title' => 'Infuse',
            'ssl' => false,
            'port' => 80,
        ],
        'services' => [
            // these services are required but can be overriden
            'exception_handler' => 'Infuse\Services\ExceptionHandler',
            'locale' => 'Infuse\Services\Locale',
            'logger' => 'Infuse\Services\Logger',
            'method_not_allowed_handler' => 'Infuse\Services\MethodNotAllowedHandler',
            'not_found_handler' => 'Infuse\Services\NotFoundHandler',
            'router' => 'Infuse\Services\Router',
            'route_resolver' => 'Infuse\Services\RouteResolver',
            'view_engine' => 'Infuse\Services\ViewEngine',
        ],
        'sessions' => [
            'enabled' => false,
        ],
        'i18n' => [
            'locale' => 'en',
        ],
        'console' => [
            'commands' => [],
        ],
    ];

    /**
     * @staticvar Application
     */
    private static $default;

    /**
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        parent::__construct();

        /* Load Configuration */

        if (!defined('INFUSE_BASE_DIR')) {
            die('INFUSE_BASE_DIR has not been defined!');
        }

        $configWithDirs = [
            'dirs' => [
                'app' => INFUSE_BASE_DIR.'/app',
                'assets' => INFUSE_BASE_DIR.'/assets',
                'public' => INFUSE_BASE_DIR.'/public',
                'temp' => INFUSE_BASE_DIR.'/temp',
                'views' => INFUSE_BASE_DIR.'/views',
            ],
        ];

        $settings = array_replace_recursive(
            static::$baseConfig,
            $configWithDirs,
            $settings);

        $config = new Config($settings);
        $this['config'] = $config;

        /* Environment */

        $this['environment'] = $environment = $config->get('app.environment');

        /* Error Reporting */

        ini_set('display_errors', $environment !== self::ENV_PRODUCTION);
        ini_set('log_errors', 1);
        error_reporting(E_ALL | E_STRICT);

        /* Base URL */

        $this['base_url'] = function () use ($config) {
            $url = (($config->get('app.ssl')) ? 'https' : 'http').'://';
            $url .= $config->get('app.hostname');
            $port = $config->get('app.port');
            $url .= ((!in_array($port, [0, 80, 443])) ? ':'.$port : '').'/';

            return $url;
        };

        /* Services  */

        foreach ($config->get('services') as $name => $class) {
            $this[$name] = new $class($this);
        }

        // set the last created app instance
        self::$default = $this;
    }

    /**
     * Gets the last created Application instance.
     *
     * @return Application
     */
    public static function getDefault()
    {
        return self::$default;
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
     * Runs the application.
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
     * Builds a response to an incoming request by routing
     * it through the application.
     *
     * @param Request $req
     *
     * @return Response
     */
    public function handleRequest(Request $req)
    {
        // set host name from request if not already set
        $config = $this['config'];
        if (!$config->get('app.hostname')) {
            $config->set('app.hostname', $req->host());
        }

        $res = new Response();
        try {
            // determine route by dispatching to router
            $routeInfo = $this['router']->dispatch($req->method(), $req->path());

            // set any route arguments on the request
            if (isset($routeInfo[2])) {
                $req->setParams($routeInfo[2]);
            }

            // start a session (TODO move to middleware)
            $this->startSession($req);

            $res = $this->executeMiddleware($req, $res);

            // resolve the route (TODO move to middleware)
            if ($routeInfo[0] === Dispatcher::FOUND) {
                return $this['route_resolver']->resolve($routeInfo[1], $req, $res, $routeInfo[2]);
            }

            // handle 405 Method Not Allowed errors
            if ($routeInfo[0] === Dispatcher::METHOD_NOT_ALLOWED) {
                return $this['method_not_allowed_handler']($req, $res, $routeInfo[1]);
            }

            // handle 404 Not Found errors
            return $this['not_found_handler']($req, $res);
        } catch (\Exception $e) {
            return $this['exception_handler']($e, $req, $res);
        }
    }

    ////////////////////////
    // MIDDLEWARE
    ////////////////////////

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
        $hostname = $config->get('app.hostname');
        ini_set('session.use_trans_sid', false);
        ini_set('session.use_only_cookies', true);
        ini_set('url_rewriter.tags', '');
        ini_set('session.gc_maxlifetime', $lifetime);

        // set the session name
        $sessionTitle = $config->get('app.title').'-'.$hostname;
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
            $handler = new $class($this);
            $handler::registerHandler($handler);
        }

        session_start();

        // fix the session cookie
        Utility::setCookieFixDomain(
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
     * Executes the middleware.
     *
     * @param Request  $req
     * @param Response $res
     *
     * @return Response $res
     */
    public function executeMiddleware(Request $req, Response $res)
    {
        foreach ($this->getMiddleware() as $module) {
            $class = 'App\\'.$module.'\Controller';
            $controller = new $class();
            if (method_exists($controller, 'setApp')) {
                $controller->setApp($this);
            }
            $controller->middleware($req, $res);
        }

        return $res;
    }

    ////////////////////////
    // CONSOLE
    ////////////////////////

    /**
     * Gets a console instance for this application.
     *
     * @return \Infuse\Console\Application
     */
    public function getConsole()
    {
        return new Console\Application($this);
    }

    ////////////////////////
    // Magic Methods
    ////////////////////////

    public function __get($k)
    {
        return $this[$k];
    }

    public function __isset($k)
    {
        return isset($this[$k]);
    }
}
