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

class MethodNotAllowedHandler
{
    public function __invoke($app)
    {
        return new \Infuse\MethodNotAllowedHandler();
    }
}
