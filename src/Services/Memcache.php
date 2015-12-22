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

class Memcache
{
    public function __invoke($app)
    {
        $memcacheConfig = $app['config']->get('memcache');
        $memcache = new \Memcache();
        $memcache->connect($memcacheConfig['host'], $memcacheConfig['port']);

        return $memcache;
    }
}
