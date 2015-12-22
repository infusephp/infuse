<?php

use Infuse\Application;
use Infuse\Queue;
use Infuse\Services\QueueDriver;

class QueueDriverTest extends PHPUnit_Framework_TestCase
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
