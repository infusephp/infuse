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
use Infuse\Services\Router;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class RouterTest extends MockeryTestCase
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
