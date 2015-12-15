<?php

namespace App\Services;

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
