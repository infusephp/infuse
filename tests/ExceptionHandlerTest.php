<?php

use Infuse\Application;
use Infuse\ExceptionHandler;
use Infuse\Request;
use Infuse\Response;

class ExceptionHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $app = new Application();
        $handler = new ExceptionHandler();
        $handler->setApp($app);
        $e = new Exception();
        $req = new Request();
        $res = new Response();
        $this->assertEquals($res, $handler($e, $req, $res));
        $this->assertEquals(500, $res->getCode());
    }
}
