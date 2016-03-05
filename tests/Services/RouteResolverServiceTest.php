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
use Infuse\Services\RouteResolver;

class RouteResolverServiceTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $config = [
            'router' => [
                'namespace' => 'namespace',
                'defaultController' => 'controller',
                'defaultAction' => 'action',
            ],
        ];
        $app = new Application($config);
        $service = new RouteResolver();
        $resolver = $service($app);

        $this->assertInstanceOf('Infuse\RouteResolver', $resolver);
        $this->assertEquals($app, $resolver->getApp());

        $this->assertEquals('namespace', $resolver->getNamespace());
        $this->assertEquals('controller', $resolver->
            getDefaultController());
        $this->assertEquals('action', $resolver->getDefaultAction());
    }
}
