<?php

use Infuse\Application;
use Infuse\Services\ModelDriver;
use Pulsar\Model;

class ModelDriverTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $config = [
            'models' => [
                'driver' => 'Pulsar\Driver\DatabaseDriver',
            ],
        ];
        $app = new Application($config);
        $service = new ModelDriver($app);
        $this->assertInstanceOf('Pulsar\Driver\DatabaseDriver', Model::getDriver());

        $driver = $service($app);
        $this->assertInstanceOf('Pulsar\Driver\DatabaseDriver', $driver);
    }
}
