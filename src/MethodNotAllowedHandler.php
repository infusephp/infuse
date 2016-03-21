<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
namespace Infuse;

class MethodNotAllowedHandler
{
    /**
     * @param Request  $req
     * @param Response $res
     * @param array    $allowedMethods
     *
     * @return Response
     */
    public function __invoke($req, $res, $allowedMethods)
    {
        if ($req->isHtml()) {
            $res->render(new View('method_not_allowed', [
                'title' => 'Method Not Allowed',
                'allowedMethods' => $allowedMethods, ]));
        }

        return $res->setCode(405);
    }
}
