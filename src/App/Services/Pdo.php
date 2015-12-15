<?php

namespace App\Services;

use Infuse\Utility as U;

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

        $user = U::array_value($dbSettings, 'user');
        $password = U::array_value($dbSettings, 'password');

        try {
            $pdo = new \PDO($dsn, $user, $password);
        } catch (\Exception $e) {
            $app['logger']->emergency($e);
            die('Could not connect to database.');
        }

        if ($config->get('site.production-level')) {
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
        } else {
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        return $pdo;
    }
}
