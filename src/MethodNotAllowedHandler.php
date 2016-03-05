<?php

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
            $res->render(new View('method_not_allowed', ['title' => '405']));
        }

        return $res->setCode(405);
    }
}
