<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
use Infuse\Application;
use Infuse\Services\Redis;

class RedisTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $config = [
            'redis' => [
                'host' => 'localhost',
                'port' => 6379,
            ],
        ];
        $app = new Application($config);
        $service = new Redis();
        $redis = $service($app);

        $this->assertInstanceOf('Redis', $redis);
    }
}