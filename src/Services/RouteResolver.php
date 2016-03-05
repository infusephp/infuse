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

class RouteResolver
{
    public function __invoke($app)
    {
        $handler = new \Infuse\RouteResolver();
        $handler->setApp($app);

        $config = $app['config'];
        if ($namespace = $config->get('router.namespace')) {
            $handler->setNamespace($namespace);
        }

        if ($defaultController = $config->get('router.defaultController')) {
            $handler->setDefaultController($defaultController);
        }

        if ($defaultAction = $config->get('router.defaultAction')) {
            $handler->setDefaultAction($defaultAction);
        }

        return $handler;
    }
}
