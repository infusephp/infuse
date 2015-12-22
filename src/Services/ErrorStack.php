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

class ErrorStack
{
    public function __invoke($app)
    {
        return new \Infuse\ErrorStack($app);
    }
}
