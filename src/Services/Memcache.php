<?php

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
