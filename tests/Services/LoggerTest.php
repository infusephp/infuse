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
use Infuse\Services\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function testInvoke()
    {
        $config = [
            'app' => [
                'hostname' => 'example.com',
            ],
            'logger' => [
                'enabled' => true,
            ],
        ];
        $app = new Application($config);
        $service = new Logger($app);
        $logger = $service($app);

        $this->assertInstanceOf('Monolog\Logger', $logger);
    }

    public function testInvokeDisabled()
    {
        $config = [
            'app' => [
                'hostname' => 'example.com',
            ],
            'logger' => [
                'enabled' => false,
            ],
        ];
        $app = new Application($config);
        $service = new Logger($app);
        $logger = $service($app);

        $this->assertInstanceOf('Monolog\Logger', $logger);
    }
}
