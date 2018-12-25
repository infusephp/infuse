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

use Error;
use Infuse\Application;
use Infuse\PhpErrorHandler;
use Infuse\Request;
use Infuse\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PhpErrorHandlerTest extends MockeryTestCase
{
    public function testInvoke()
    {
        $app = new Application(['dirs' => ['views' => __DIR__.'/views']]);
        $handler = new PhpErrorHandler();
        $handler->setApp($app);
        $e = new Error();
        $req = new Request([], [], [], [], ['HTTP_ACCEPT' => 'text/html']);
        $res = new Response();
        $this->assertEquals($res, $handler($req, $res, $e));
        $this->assertEquals(500, $res->getCode());
        $this->assertEquals('php error', $res->getBody());
    }
}
