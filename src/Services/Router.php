<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
namespace Infuse\Services;

class Router
{
    public function __invoke($app)
    {
        $config = $app['config'];
        $routes = (array) $config->get('routes');
        $settings = (array) $config->get('router');

        return new \Infuse\Router($routes, $settings);
    }
}
