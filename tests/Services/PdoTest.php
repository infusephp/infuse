<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @see http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */

namespace Infuse\Test\Services;

use Infuse\Application;
use Infuse\Services\Pdo as PdoService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PDO;

class PdoTest extends MockeryTestCase
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
        $service = new PdoService();

        $pdo = $service($app);
        $this->assertInstanceOf('PDO', $pdo);
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
    }

    public function testInvokeConnString()
    {
        $config = [
            'database' => [
                'dsn' => 'mysql:host=localhost;dbname=mydb',
                'user' => 'root',
                'password' => '',
            ],
        ];
        $app = new Application($config, Application::ENV_PRODUCTION);
        $service = new PdoService();

        $pdo = $service($app);
        $this->assertInstanceOf('PDO', $pdo);
        $this->assertEquals(PDO::ERRMODE_WARNING, $pdo->getAttribute(PDO::ATTR_ERRMODE));
    }
}
