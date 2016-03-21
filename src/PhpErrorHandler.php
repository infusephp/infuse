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

class PhpErrorHandler
{
    use HasApp;

    /**
     * @param Request  $req
     * @param Response $res
     * @param \Error   $e
     *
     * @return Response
     */
    public function __invoke($req, $res, \Error $e)
    {
        $this->app['logger']->error('A PHP error occurred while handling a request.', ['exception' => $e]);

        if ($req->isHtml()) {
            $res->render(new View('php_error', [
                'title' => 'Internal Server Error',
                'error' => $e, ]));
        }

        return $res->setCode(500);
    }
}
