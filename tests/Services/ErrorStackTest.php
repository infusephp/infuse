<?php

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
