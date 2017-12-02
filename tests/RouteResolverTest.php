<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
use Infuse\Request;
use Infuse\Response;
use Infuse\RouteResolver;
use Infuse\View;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Test\TestController;

include 'TestController.php';

class RouteResolverTest extends MockeryTestCase
{
    public function setUp()
    {
        MockController::$staticRouteCalled = false;
        MockController::$indexRouteCalled = false;
        MockController::$appInjected = false;
        MockController::$args = [];
    }

    public function testNamespace()
    {
        $resolver = new RouteResolver();
        $this->assertNull($resolver->getNamespace());
        $resolver->setNamespace('test');
        $this->assertEquals('test', $resolver->getNamespace());
    }

    public function testDefaultController()
    {
        $resolver = new RouteResolver();
        $this->assertNull($resolver->getDefaultController());
        $resolver->setDefaultController('test');
        $this->assertEquals('test', $resolver->getDefaultController());
    }

    public function testDefaultAction()
    {
        $resolver = new RouteResolver();
        $this->assertEquals('index', $resolver->getDefaultAction());
        $resolver->setDefaultAction('test');
        $this->assertEquals('test', $resolver->getDefaultAction());
    }

    public function testResolve()
    {
        $resolver = new RouteResolver();
        $resolver->setDefaultController('MockController');

        $req = new Request();
        $res = new Response();

        $this->assertEquals($res, $resolver->resolve('staticRoute', $req, $res, ['test' => true]));

        $this->assertTrue(MockController::$staticRouteCalled);
        $this->assertTrue(MockController::$appInjected);
        $this->assertEquals(['test' => true], MockController::$args);
    }

    public function testResolveIndex()
    {
        $resolver = new RouteResolver();

        $req = new Request();
        $res = new Response();

        $this->assertEquals($res, $resolver->resolve(['MockController'], $req, $res, []));

        $this->assertTrue(MockController::$indexRouteCalled);
        $this->assertTrue(MockController::$appInjected);
    }

    public function testResolveNonExistentController()
    {
        $this->expectException(Exception::class);

        $resolver = new RouteResolver();

        $req = new Request();
        $res = new Response();
        $resolver->resolve(['BogusController', 'who_cares'], $req, $res, []);
    }

    public function testResolveControllerParam()
    {
        $resolver = new RouteResolver();
        $resolver->setDefaultController('BogusController');

        $req = new Request();
        $req->setParams(['controller' => 'MockController']);
        $res = new Response();

        $this->assertEquals($res, $resolver->resolve('staticRoute', $req, $res, []));

        $this->assertTrue(MockController::$staticRouteCalled);
        $this->assertTrue(MockController::$appInjected);
    }

    public function testResolvePresetParameters()
    {
        $extraParams = [
            'test' => true,
            'hello' => 'world',
        ];

        $resolver = new RouteResolver();

        $req = new Request();
        $res = new Response();

        $this->assertEquals($res, $resolver->resolve(['MockController', 'staticRoute', $extraParams], $req, $res, []));

        $this->assertTrue(MockController::$staticRouteCalled);
        $this->assertTrue(MockController::$appInjected);
        $this->assertEquals($extraParams, $req->params());
    }

    public function testResolveNamespace()
    {
        $resolver = new RouteResolver();
        $resolver->setDefaultController('TestController')
                 ->setNamespace('Test');

        $req = new Request();
        $res = new Response();

        $this->assertEquals($res, $resolver->resolve('route', $req, $res, []));

        $this->assertTrue(TestController::$called);
        $this->assertTrue(TestController::$appInjected);
    }

    public function testResolveView()
    {
        $resolver = new RouteResolver();

        $view = new View('test');
        MockController::$view = $view;

        $req = new Request();

        $res = Mockery::mock('Infuse\Response');
        $res->shouldReceive('render')
            ->withArgs([$view])
            ->once();

        $this->assertEquals($res, $resolver->resolve(['MockController', 'view'], $req, $res, []));
    }

    public function testResolveClosure()
    {
        $resolver = new RouteResolver();

        $req = new Request();
        $res = new Response();

        $test = false;
        $handler = function ($req, $res) use (&$test) {
            $test = true;
        };

        $this->assertEquals($res, $resolver->resolve($handler, $req, $res, []));

        $this->assertTrue($test);
    }
}

class MockController
{
    public static $appInjected = false;
    public static $staticRouteCalled = false;
    public static $indexRouteCalled = false;
    public static $args = [];
    public static $view;

    public function setApp($app)
    {
        self::$appInjected = true;
    }

    public function staticRoute(Request $req, Response $res, array $args)
    {
        self::$staticRouteCalled = true;
        self::$args = $args;
    }

    public function index(Request $req, Response $res, array $args)
    {
        self::$indexRouteCalled = true;
        self::$args = $args;
    }

    public function view(Request $req, Response $res, array $args)
    {
        self::$args = $args;

        return self::$view;
    }

    public function fail(Request $req, Response $res, array $args)
    {
        throw new \Exception('FAIL');
    }
}
