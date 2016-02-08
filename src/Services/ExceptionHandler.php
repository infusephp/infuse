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

class ExceptionHandler
{
    public function __invoke($app)
    {
        $handler = new \Infuse\ExceptionHandler();
        $handler->setApp($app);

        return $handler;
    }
}
