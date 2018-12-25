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
use Infuse\Services\RouteResolver;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class RouteResolverServiceTest extends MockeryTestCase
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
