<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */

namespace Infuse\Services;

use Infuse\Application;

class Pdo
{
    public function __invoke($app)
    {
        $config = $app['config'];
        $dbSettings = (array) $config->get('database');

        if (isset($dbSettings['dsn'])) {
            $dsn = $dbSettings['dsn'];
        } else { // generate the dsn
            $dsn = $dbSettings['type'].':host='.$dbSettings['host'].';dbname='.$dbSettings['name'];
        }

        $user = array_value($dbSettings, 'user');
        $password = array_value($dbSettings, 'password');

        $pdo = new \PDO($dsn, $user, $password);

        if ($app['environment'] === Application::ENV_PRODUCTION) {
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
        } else {
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        return $pdo;
    }
}
