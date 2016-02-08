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
     */
    public function __invoke(\Exception $e, $req, $res)
    {
        $res->setCode(500);
        $this->app['logger']->error('An uncaught exception occurred while handling a request.', ['exception' => $e]);
    }
}
