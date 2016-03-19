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
use Infuse\NotFoundHandler;
use Infuse\Request;
use Infuse\Response;

class NotFoundHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $app = new Application(['dirs' => ['views' => __DIR__.'/views']]);
        $handler = new NotFoundHandler();
        $req = new Request([], [], [], [], ['HTTP_ACCEPT' => 'text/html']);
        $res = new Response();
        $this->assertEquals($res, $handler($req, $res, ['POST']));
        $this->assertEquals(404, $res->getCode());
        $this->assertEquals('not found', $res->getBody());
    }
}
