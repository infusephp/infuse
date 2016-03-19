<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
namespace Infuse\Middleware;

use FastRoute\Dispatcher;
use Infuse\HasApp;
use Infuse\Request;
use Infuse\Response;

class DispatchMiddleware
{
    use HasApp;

    public function __invoke(Request $req, Response $res, callable $next)
    {
        $routeInfo = $this->app['routeInfo'];

        // resolve the route
        if ($routeInfo[0] === Dispatcher::FOUND) {
            return $this->app['route_resolver']->resolve($routeInfo[1], $req, $res, $routeInfo[2]);
        }

        // handle 405 Method Not Allowed errors
        if ($routeInfo[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            return $this->app['method_not_allowed_handler']($req, $res, $routeInfo[1]);
        }

        // handle 404 Not Found errors
        return $this->app['not_found_handler']($req, $res);
    }
}
