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
use Infuse\Services\ErrorStack;

class ErrorStackTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $app = new Application();
        $service = new ErrorStack();
        $stack = $service($app);

        $this->assertInstanceOf('Infuse\ErrorStack', $stack);
    }
}
