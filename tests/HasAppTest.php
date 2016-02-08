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
use Infuse\HasApp;

class HasAppTest extends PHPUnit_Framework_TestCase
{
    public function testGetDefault()
    {
        $app = new Application();
        $class = new SomeClass();
        $this->assertEquals($app, $class->getApp());
    }

    public function testGetApp()
    {
        $app = Mockery::mock('Infuse\Application');
        $class = new SomeClass();
        $this->assertEquals($class, $class->setApp($app));
        $this->assertEquals($app, $class->getApp());

        $class2 = new SomeClass();
        $app2 = Mockery::mock('Infuse\Application');
        $class2->setApp($app2);
        $this->assertTrue($class->getApp() !== $class2->getApp());
    }
}

class SomeClass
{
    use HasApp;
}
