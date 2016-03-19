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

use Infuse\Middleware\DispatchMiddleware;
use Pimple\Container;
use SplStack;

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
     * @var SplStack
     */
    private $middleware;

    /**
     * @param array  $settings
     * @param string $environment
     */
    public function __construct(array $settings = [], $environment = self::ENV_DEVELOPMENT)
    {
        parent::__construct();

        if (!defined('INFUSE_BASE_DIR')) {
            die('INFUSE_BASE_DIR has not been defined!');
        }

        /* Load Configuration */

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
        $this['environment'] = $environment;

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
            $this['routeInfo'] = $routeInfo;

            // set any route arguments on the request
            if (isset($routeInfo[2])) {
                $req->setParams($routeInfo[2]);
            }

            // the dispatch middleware is the final step
            $dispatch = new DispatchMiddleware();
            $dispatch->setApp($this);
            $this->middleware($dispatch);

            // the request is handled by returning the response
            // generated by the middleware chain
            return $this->runMiddleware($req, $res);
        } catch (\Exception $e) {
            return $this['exception_handler']($e, $req, $res);
        }
    }

    ////////////////////////
    // MIDDLEWARE
    ////////////////////////

    /**
     * Adds middleware to the application.
     *
     * The middleware must be callable / invokable with the following
     * method signature:
     *    middlewareFn(Request $req, Response $res, callable $next)
     *
     * Middleware is called in LIFO order. Each middleware step
     * is expected to return a Response object. In order to continue
     * processing the middleware chain then call $next($req, $res).
     * If a middleware is to be the final step then simply return
     * a Response object instead of calling $next.
     *
     * @param callable $middleware
     *
     * @return self
     */
    public function middleware(callable $middleware)
    {
        $this->initMiddleware()->unshift($middleware);

        return $this;
    }

    /**
     * Runs the middleware chain.
     *
     * @param Request  $req
     * @param Response $res
     *
     * @return Response $res
     */
    public function runMiddleware(Request $req, Response $res)
    {
        $this->initMiddleware()->rewind();

        return $this->nextMiddleware($req, $res);
    }

    /**
     * Initializes the middleware stack.
     *
     * @return SplStack
     */
    private function initMiddleware()
    {
        // only need to initialize once
        if (!$this->middleware) {
            $this->middleware = new SplStack();
        }

        return $this->middleware;
    }

    /**
     * Calls the next middleware in the chain. 
     * DO NOT call directly.
     *
     * @param Request  $req
     * @param Response $res
     *
     * @return Response
     */
    public function nextMiddleware(Request $req, Response $res)
    {
        $middleware = $this->middleware->current();

        // base case - no middleware left
        if (!$middleware) {
            return $res;
        }

        // otherwise, call next middleware
        $this->middleware->next();

        return $middleware($req, $res, [$this, 'nextMiddleware']);
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
