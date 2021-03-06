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

use Infuse\Application;
use Infuse\MethodNotAllowedHandler;
use Infuse\Request;
use Infuse\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class MethodNotAllowedHandlerTest extends MockeryTestCase
{
    public function testInvoke()
    {
        $app = new Application(['dirs' => ['views' => __DIR__.'/views']]);
        $handler = new MethodNotAllowedHandler();
        $req = new Request([], [], [], [], ['HTTP_ACCEPT' => 'text/html']);
        $res = new Response();
        $this->assertEquals($res, $handler($req, $res, ['POST']));
        $this->assertEquals(405, $res->getCode());
        $this->assertEquals('method not allowed', $res->getBody());
    }
}
