<?php

use Infuse\Application;
use Infuse\Services\Router;

class RouterTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $config = [
            'routes' => [
                'get /test/' => 'test',
            ],
        ];
        $app = new Application($config);
        $service = new Router();
        $router = $service($app);

        $this->assertInstanceOf('Infuse\Router', $router);
        $this->assertEquals([['GET', '/test/', 'test']], $router->getRoutes());
    }
}
