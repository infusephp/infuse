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

class Redis
{
    public function __invoke($app)
    {
        $redisConfig = $app['config']->get('redis');
        $redis = new \Redis();
        $redis->connect($redisConfig['host'], $redisConfig['port']);

        return $redis;
    }
}
