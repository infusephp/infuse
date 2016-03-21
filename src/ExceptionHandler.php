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

class ExceptionHandler
{
    use HasApp;

    /**
     * @param Request    $req
     * @param Response   $res
     * @param \Exception $e
     *
     * @return Response
     */
    public function __invoke($req, $res, \Exception $e)
    {
        $this->app['logger']->error('An uncaught exception occurred while handling a request.', ['exception' => $e]);

        if ($req->isHtml()) {
            $res->render(new View('exception', [
                'title' => 'Internal Server Error',
                'exception' => $e, ]));
        }

        return $res->setCode(500);
    }
}
