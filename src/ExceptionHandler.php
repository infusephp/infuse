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
     * @param \Exception $e
     * @param Request    $req
     * @param Response   $res
     *
     * @return Response
     */
    public function __invoke(\Exception $e, $req, $res)
    {
        $this->app['logger']->error('An uncaught exception occurred while handling a request.', ['exception' => $e]);

        return $res->setCode(500);
    }
}
