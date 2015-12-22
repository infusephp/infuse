<?php

use Infuse\Application;
use Infuse\Services\Database;

class DatabaseTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $config = [
            'database' => [
                'type' => 'mysql',
                'host' => 'localhost',
                'name' => 'mydb',
                'user' => 'root',
                'password' => '',
            ],
        ];
        $app = new Application($config);
        $app['pdo'] = Mockery::mock();
        $service = new Database();

        $db = $service($app);
        $this->assertInstanceOf('JAQB\QueryBuilder', $db);
        $this->assertEquals($app['pdo'], $db->getPdo());
    }
}
