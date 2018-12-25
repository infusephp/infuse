<?php

namespace Infuse;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Converts Symfony request/response objects to
 * Infuse request/response objects, and vice-versa.
 */
class SymfonyHttpBridge
{
    /**
     * Converts a Symfony request object into an Infuse request object.
     *
     * @param SymfonyRequest $request
     *
     * @return Request
     */
    public function convertSymfonyRequest(SymfonyRequest $request)
    {
        $session = $request->getSession();
        if ($session) {
            $session = $session->all();
        } else {
            $session = [];
        }

        $req = new Request($request->query->all(), $request->request->all(), $request->cookies->all(), $request->files->all(), $request->server->all(), $session);
        $req->setParams($request->attributes->all());

        return $req;
    }

    /**
     * Converts an Infuse response object into a Symfony response object.
     *
     * @param Response $res
     *
     * @return SymfonyResponse
     */
    public function convertInfuseResponse(Response $res)
    {
        $response = new SymfonyResponse($res->getBody(), $res->getCode(), $res->headers());

        // transfer cookies
        foreach ($res->cookies() as $name => $params) {
            list($value, $expire, $path, $domain, $secure, $httponly) = $params;
            $cookie = new Cookie($name, $value, $expire, $path, $domain, $secure, $httponly);
            $response->headers->setCookie($cookie);
        }

        return $response;
    }
}
