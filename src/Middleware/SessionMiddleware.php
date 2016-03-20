<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
namespace Infuse\Middleware;

use Infuse\HasApp;
use Infuse\Request;
use Infuse\Response;
use Infuse\Utility;

class SessionMiddleware
{
    use HasApp;

    public function __invoke(Request $req, Response $res, callable $next)
    {
        $config = $this->app['config'];
        if (!$config->get('sessions.enabled') || $req->isApi()) {
            return $next($req, $res);
        }

        $lifetime = $config->get('sessions.lifetime');
        $hostname = $config->get('app.hostname');
        ini_set('session.use_trans_sid', false);
        ini_set('session.use_only_cookies', true);
        ini_set('url_rewriter.tags', '');
        ini_set('session.gc_maxlifetime', $lifetime);

        // set the session name
        $sessionTitle = $config->get('app.title').'-'.$hostname;
        $safeSessionTitle = str_replace(['.', ' ', "'", '"'], ['', '_', '', ''], $sessionTitle);
        session_name($safeSessionTitle);

        // set the session cookie parameters
        session_set_cookie_params(
            $lifetime, // lifetime
            '/', // path
            '.'.$hostname, // domain
            $req->isSecure(), // secure
            true // http only
        );

        // install any custom session handlers
        $class = $config->get('sessions.driver');
        if ($class) {
            $handler = new $class($this->app);
            $handler::registerHandler($handler);
        }

        session_start();

        // fix the session cookie
        Utility::setCookieFixDomain(
            session_name(),
            session_id(),
            time() + $lifetime,
            '/',
            $hostname,
            $req->isSecure(),
            true
        );

        // make the newly started session in our request
        $req->setSession($_SESSION);

        return $next($req, $res);
    }
}
