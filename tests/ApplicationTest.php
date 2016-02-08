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
use Infuse\Request;

class ApplicationTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        date_default_timezone_set('UTC');
    }

    public function testGetDefault()
    {
        $app = new Application();
        $this->assertEquals($app, Application::getDefault());

        $app2 = new Application();
        $this->assertEquals($app2, Application::getDefault());
    }

    public function testConfig()
    {
        $app = new Application(['test' => true]);

        $expected = [
            'test' => true,
            'app' => [
                'title' => 'Infuse',
                'ssl' => false,
                'port' => 80,
                'environment' => 'development',
            ],
            'services' => [
                'exception_handler' => 'Infuse\Services\ExceptionHandler',
                'locale' => 'Infuse\Services\Locale',
                'logger' => 'Infuse\Services\Logger',
                'router' => 'Infuse\Services\Router',
                'view_engine' => 'Infuse\Services\ViewEngine',
            ],
            'sessions' => [
                'enabled' => false,
            ],
            'dirs' => [
                'app' => INFUSE_BASE_DIR.'/app',
                'assets' => INFUSE_BASE_DIR.'/assets',
                'public' => INFUSE_BASE_DIR.'/public',
                'temp' => INFUSE_BASE_DIR.'/temp',
                'views' => INFUSE_BASE_DIR.'/views',
            ],
            'i18n' => [
                'locale' => 'en',
            ],
            'console' => [
                'commands' => [],
            ],
        ];

        $config = $app['config'];
        $this->assertInstanceOf('Infuse\Config', $config);
        $this->assertEquals($expected, $config->all());
    }

    public function testDefaultServices()
    {
        $config = [
            'app' => [
                'hostname' => 'example.com',
                'ssl' => true,
            ],
        ];

        $app = new Application($config);

        $this->assertEquals('https://example.com/', $app['base_url']);
        $this->assertEquals('development', $app['environment']);

        $this->assertInstanceOf('Monolog\Logger', $app['logger']);
        $this->assertInstanceOf('Infuse\Locale', $app['locale']);
        $this->assertInstanceOf('Infuse\Router', $app['router']);
        $this->assertInstanceOf('Infuse\ViewEngine\PHP', $app['view_engine']);

        // test magic methods
        $this->assertTrue(isset($app->logger));
        $this->assertInstanceOf('Monolog\Logger', $app->logger);
    }

    public function testGet()
    {
        $app = new Application();
        $handler = function () {};

        $this->assertEquals($app, $app->get('/users/{id}', $handler));

        $this->assertEquals([['GET', '/users/{id}', $handler]], $app['router']->getRoutes());
    }

    public function testPost()
    {
        $app = new Application();
        $handler = function () {};

        $this->assertEquals($app, $app->post('/users', $handler));

        $this->assertEquals([['POST', '/users', $handler]], $app['router']->getRoutes());
    }

    public function testPut()
    {
        $app = new Application();
        $handler = function () {};

        $this->assertEquals($app, $app->put('/users/{id}', $handler));

        $this->assertEquals([['PUT', '/users/{id}', $handler]], $app['router']->getRoutes());
    }

    public function testDelete()
    {
        $app = new Application();
        $handler = function () {};

        $this->assertEquals($app, $app->delete('/users/{id}', $handler));

        $this->assertEquals([['DELETE', '/users/{id}', $handler]], $app['router']->getRoutes());
    }

    public function testPatch()
    {
        $app = new Application();
        $handler = function () {};

        $this->assertEquals($app, $app->patch('/users/{id}', $handler));

        $this->assertEquals([['PATCH', '/users/{id}', $handler]], $app['router']->getRoutes());
    }

    public function testOptions()
    {
        $app = new Application();
        $handler = function () {};

        $this->assertEquals($app, $app->options('/users/{id}', $handler));

        $this->assertEquals([['OPTIONS', '/users/{id}', $handler]], $app['router']->getRoutes());
    }

    public function testMap()
    {
        $app = new Application();
        $handler = function () {};

        $this->assertEquals($app, $app->map('GET', '/users/{id}', $handler));

        $this->assertEquals([['GET', '/users/{id}', $handler]], $app['router']->getRoutes());
    }

    public function testGetMiddleware()
    {
        $config = ['modules' => ['middleware' => ['test']]];
        $app = new Application($config);

        $this->assertEquals(['test'], $app->getMiddleware());
    }

    public function testHandleRequest()
    {
        $app = new Application();
        $router = Mockery::mock();
        $router->shouldReceive('route');
        $app['router'] = $router;

        $req = new Request();
        $res = $app->handleRequest($req);
        $this->assertInstanceOf('Infuse\Response', $res);

        $this->assertEquals(200, $res->getCode());
    }

    public function testHandleRequestException()
    {
        $app = new Application();
        $router = Mockery::mock();
        $router->shouldReceive('route')
               ->andThrow(new Exception());
        $app['router'] = $router;

        $req = new Request();
        $res = $app->handleRequest($req);

        $this->assertEquals(500, $res->getCode());
    }

    public function testRun()
    {
        $this->markTestIncomplete();
    }

    public function testGetConsole()
    {
        $app = new Application();

        $console = $app->getConsole();
        $this->assertInstanceOf('Infuse\Console\Application', $console);
    }
}
