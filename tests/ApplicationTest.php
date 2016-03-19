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
use Infuse\Response;

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
        $app = new Application(['test' => true], 'development');

        $expected = [
            'test' => true,
            'app' => [
                'title' => 'Infuse',
                'ssl' => false,
                'port' => 80,
            ],
            'services' => [
                'exception_handler' => 'Infuse\Services\ExceptionHandler',
                'locale' => 'Infuse\Services\Locale',
                'logger' => 'Infuse\Services\Logger',
                'method_not_allowed_handler' => 'Infuse\Services\MethodNotAllowedHandler',
                'not_found_handler' => 'Infuse\Services\NotFoundHandler',
                'router' => 'Infuse\Services\Router',
                'route_resolver' => 'Infuse\Services\RouteResolver',
                'view_engine' => 'Infuse\Services\ViewEngine',
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

        $this->assertInstanceOf('Infuse\ExceptionHandler', $app['exception_handler']);
        $this->assertInstanceOf('Infuse\Locale', $app['locale']);
        $this->assertInstanceOf('Monolog\Logger', $app['logger']);
        $this->assertInstanceOf('Infuse\MethodNotAllowedHandler', $app['method_not_allowed_handler']);
        $this->assertInstanceOf('Infuse\NotFoundHandler', $app['not_found_handler']);
        $this->assertInstanceOf('Infuse\Router', $app['router']);
        $this->assertInstanceOf('Infuse\RouteResolver', $app['route_resolver']);
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

    public function testHandleRequest()
    {
        $app = new Application();
        $resolverResponse = new Response();

        $called = false;
        $route = function () use (&$called) {
            $called = true;
        };

        $router = Mockery::mock();
        $router->shouldReceive('dispatch')
               ->andReturn([1, $route, ['test' => true]]);
        $app['router'] = $router;

        $req = new Request();
        $res = $app->handleRequest($req);
        $this->assertInstanceOf('Infuse\Response', $res);

        $this->assertTrue($called);

        $this->assertEquals(200, $res->getCode());
        $this->assertEquals($resolverResponse, $res);

        $this->assertEquals(['test' => true], $req->params());
    }

    public function testHandleRequestNotFound()
    {
        $app = new Application();

        $router = Mockery::mock();
        $router->shouldReceive('dispatch')
               ->andReturn([0]);
        $app['router'] = $router;

        $req = new Request();
        $res = $app->handleRequest($req);
        $this->assertInstanceOf('Infuse\Response', $res);
        $this->assertEquals(404, $res->getCode());
    }

    public function testHandleRequestMethodNotAllowed()
    {
        $app = new Application();

        $router = Mockery::mock();
        $router->shouldReceive('dispatch')
               ->andReturn([2, ['POST']]);
        $app['router'] = $router;

        $req = new Request();
        $res = $app->handleRequest($req);
        $this->assertInstanceOf('Infuse\Response', $res);
        $this->assertEquals(405, $res->getCode());
    }

    public function testHandleRequestException()
    {
        $app = new Application();
        $router = Mockery::mock();
        $router->shouldReceive('dispatch')
               ->andThrow(new Exception());
        $app['router'] = $router;

        $req = new Request();
        $res = $app->handleRequest($req);
        $this->assertInstanceOf('Infuse\Response', $res);

        $this->assertEquals(500, $res->getCode());
    }

    public function testRun()
    {
        $app = new Application();
        $app->post('/test/{a1}/{a2}/{a3}', function ($req, $res) {
            $res->setBody('woo'.$req->params('a1').$req->params('a2').$req->params('a3'));
        });

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test/1/2/3';

        ob_start();
        $this->assertEquals($app, $app->run());
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('woo123', $output);
    }

    public function testMiddleware()
    {
        $a = function ($req, $res, $next) {
            $res->setBody($res->getBody().'a_before ');
            $res = $next($req, $res);

            return $res->setBody($res->getBody().'a_after');
        };

        $b = function ($req, $res, $next) {
            $res->setBody($res->getBody().'b_before ');
            $res = $next($req, $res);

            return $res->setBody($res->getBody().'b_after ');
        };

        $c = function ($req, $res, $next) {
            $res->setBody($res->getBody().'c_before ');
            $res = $next($req, $res);

            return $res->setBody($res->getBody().'c_after ');
        };

        $app = new Application();
        $app->middleware($a)->middleware($b)->middleware($c);

        $req = new Request();
        $res = new Response();

        $this->assertEquals($res, $app->runMiddleware($req, $res));

        $this->assertEquals('a_before b_before c_before c_after b_after a_after', $res->getBody());
    }

    public function testGetConsole()
    {
        $config = [
            'console' => [
                'commands' => [
                    'Infuse\Console\OptimizeCommand',
                ],
            ],
        ];
        $app = new Application($config);

        $console = $app->getConsole();
        $this->assertInstanceOf('Infuse\Console\Application', $console);
    }
}
