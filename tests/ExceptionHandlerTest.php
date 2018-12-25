<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @see http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */

namespace Infuse\Test;

use Exception;
use Infuse\Application;
use Infuse\ExceptionHandler;
use Infuse\Request;
use Infuse\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ExceptionHandlerTest extends MockeryTestCase
{
    public function testInvoke()
    {
        $app = new Application(['dirs' => ['views' => __DIR__.'/views']]);
        $handler = new ExceptionHandler();
        $handler->setApp($app);
        $e = new Exception();
        $req = new Request([], [], [], [], ['HTTP_ACCEPT' => 'text/html']);
        $res = new Response();
        $this->assertEquals($res, $handler($req, $res, $e));
        $this->assertEquals(500, $res->getCode());
        $this->assertEquals('exception', $res->getBody());
    }
}
