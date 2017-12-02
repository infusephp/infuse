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
use Infuse\Queue;
use Infuse\Services\QueueDriver;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class QueueDriverTest extends MockeryTestCase
{
    public function testInvoke()
    {
        $config = [
            'queue' => [
                'driver' => 'Infuse\Queue\Driver\SynchronousDriver',
            ],
        ];
        $app = new Application($config);
        $service = new QueueDriver($app);

        $queue = new Queue('test');
        $this->assertInstanceOf('Infuse\Queue\Driver\SynchronousDriver', $queue->getDriver());

        $driver = $service($app);
        $this->assertInstanceOf('Infuse\Queue\Driver\SynchronousDriver', $driver);
    }
}
