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

class PhpErrorHandler
{
    public function __invoke($app)
    {
        $handler = new \Infuse\PhpErrorHandler();
        $handler->setApp($app);

        return $handler;
    }
}
