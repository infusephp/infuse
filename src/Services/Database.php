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

use JAQB\QueryBuilder;

class Database
{
    public function __invoke($app)
    {
        return new QueryBuilder($app['pdo']);
    }
}
