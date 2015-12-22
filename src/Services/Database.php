<?php

namespace Infuse\Services;

use JAQB\QueryBuilder;

class Database
{
    public function __invoke($app)
    {
        return new QueryBuilder($app['pdo']);
    }
}
