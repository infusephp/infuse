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
use Infuse\MethodNotAllowedHandler;
use Infuse\Request;
use Infuse\Response;
use PHPUnit\Framework\TestCase;

class MethodNotAllowedHandlerTest extends TestCase
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
