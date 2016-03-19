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

class NotFoundHandler
{
    /**
     * @param Request  $req
     * @param Response $res
     *
     * @return Response
     */
    public function __invoke($req, $res)
    {
        if ($req->isHtml()) {
            $res->render(new View('not_found', ['title' => '404']));
        }

        return $res->setCode(404);
    }
}
