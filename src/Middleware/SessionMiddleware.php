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

        // Check if sessions are disabled for the route
        $route = (array) array_value($this->app['routeInfo'], 1);
        $params = (array) array_value($route, 2);
        if (array_value($params, 'no_session')) {
            return $next($req, $res);
        }

        $lifetime = $config->get('sessions.lifetime');
        $hostname = $config->get('app.hostname');
        ini_set('session.use_trans_sid', false);
        ini_set('session.use_only_cookies', true);
        ini_set('url_rewriter.tags', '');
        ini_set('session.gc_maxlifetime', $lifetime);

        // set the session name
        $defaultSessionTitle = $config->get('app.title').'-'.$hostname;
        $sessionTitle = $config->get('sessions.name', $defaultSessionTitle);
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

        // register session_write_close as a shutdown function
        session_register_shutdown();

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
