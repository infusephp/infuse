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

use Infuse\Queue;

class QueueDriver
{
    /**
     * @var \Infuse\Queue\Driver\DriverInterface
     */
    private $driver;

    public function __construct($app)
    {
        // set up the queue driver
        $class = $app['config']->get('queue.driver');
        $this->driver = new $class($app);
        Queue::setDriver($this->driver);
    }

    public function __invoke()
    {
        return $this->driver;
    }
}
