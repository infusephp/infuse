<?php

namespace Infuse\Test;

use Infuse\Request;
use Infuse\Response;
use Infuse\SymfonyHttpBridge;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class SymfonyHttpBridgeTest extends MockeryTestCase
{
    public function testConvertSymfonyRequest()
    {
        $bridge = new SymfonyHttpBridge();
        $server = ['REQUEST_METHOD' => 'GET'];
        $request = SymfonyRequest::create('/', 'GET', ['test' => true], ['test2' => true], ['test3' => []], $server);
        $request->attributes->set('test4', true);

        $infuseRequest = $bridge->convertSymfonyRequest($request);

        $this->assertInstanceOf(Request::class, $infuseRequest);
        $this->assertEquals('GET', $infuseRequest->method());
        $this->assertEquals(['test' => true], $infuseRequest->query());
        $this->assertEquals([], $infuseRequest->request());
        $this->assertEquals(['test2' => true], $infuseRequest->cookies());
        $this->assertEquals(['test3' => []], $infuseRequest->files());
        $this->assertEquals(['test4' => true], $infuseRequest->params());
    }

    public function testConvertSymfonyRequestPost()
    {
        $bridge = new SymfonyHttpBridge();
        $server = ['REQUEST_METHOD' => 'POST'];
        $request = SymfonyRequest::create('/?query=1', 'POST', ['test' => true], ['test2' => true], ['test3' => []], $server);
        $request->attributes->set('test4', true);

        $infuseRequest = $bridge->convertSymfonyRequest($request);

        $this->assertInstanceOf(Request::class, $infuseRequest);
        $this->assertEquals('POST', $infuseRequest->method());
        $this->assertEquals(['test' => true], $infuseRequest->request());
        $this->assertEquals(['query' => 1], $infuseRequest->query());
        $this->assertEquals(['test2' => true], $infuseRequest->cookies());
        $this->assertEquals(['test3' => []], $infuseRequest->files());
        $this->assertEquals(['test4' => true], $infuseRequest->params());
    }

    public function testConvertSymfonyRequestJson()
    {
        $bridge = new SymfonyHttpBridge();
        $server = ['REQUEST_METHOD' => 'GET', 'CONTENT_TYPE' => 'application/json'];
        $request = SymfonyRequest::create('/?query=1', 'POST', [], ['test2' => true], ['test3' => []], $server, '{"test":true}');
        $request->attributes->set('test4', true);

        $infuseRequest = $bridge->convertSymfonyRequest($request);

        $this->assertInstanceOf(Request::class, $infuseRequest);
        $this->assertEquals('POST', $infuseRequest->method());
        $this->assertEquals(['test' => true], $infuseRequest->request());
        $this->assertEquals(['query' => 1], $infuseRequest->query());
        $this->assertEquals(['test2' => true], $infuseRequest->cookies());
        $this->assertEquals(['test3' => []], $infuseRequest->files());
        $this->assertEquals(['test4' => true], $infuseRequest->params());
    }

    public function testConvertInfuseResponse()
    {
        $bridge = new SymfonyHttpBridge();

        $response = new Response();
        $response->setHeader('test', true);
        $response->setCode(201);
        $response->setBody('test');
        $response->setCookie('test', '1234');

        $symfonyResponse = $bridge->convertInfuseResponse($response);

        $this->assertInstanceOf(SymfonyResponse::class, $symfonyResponse);
        $headers = $symfonyResponse->headers->all();
        unset($headers['date']);
        $this->assertEquals([
            'test' => [
                true,
            ],
            'cache-control' => [
                'no-cache, private',
            ],
            'set-cookie' => [
                'test=1234; path=/',
            ],
        ], $headers);
        $this->assertEquals(201, $symfonyResponse->getStatusCode());
        $this->assertEquals('test', $symfonyResponse->getContent());
        $cookies = $symfonyResponse->headers->getCookies();
        $this->assertCount(1, $cookies);
        $this->assertEquals('test=1234; path=/', (string) $cookies[0]);
    }
}
