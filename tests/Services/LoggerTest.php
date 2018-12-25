<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @see http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */

namespace Infuse\Test\Services;

use Infuse\Application;
use Infuse\Services\Logger;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class LoggerTest extends MockeryTestCase
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
