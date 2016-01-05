<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
use Infuse\HasApp;

class HasAppTest extends PHPUnit_Framework_TestCase
{
    public function testInject()
    {
        $app = Mockery::mock('Infuse\Application');
        $class = new SomeClass();
        $this->assertEquals($class, $class->injectApp($app));
        $this->assertEquals($app, $class->getApp());

        $class2 = new SomeClass();
        $this->assertNull($class2->getApp());
        $app2 = Mockery::mock('Infuse\Application');
        $class2->injectApp($app2);
        $this->assertTrue($class->getApp() !== $class2->getApp());
    }
}

class SomeClass
{
    use HasApp;
}
