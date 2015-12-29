<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
use Infuse\Application;
use Infuse\Services\Pdo as PdoService;

class PdoTest extends PHPUnit_Framework_TestCase
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
            'app' => [
                'environment' => Application::ENV_PRODUCTION,
            ],
            'database' => [
                'dsn' => 'mysql:host=localhost;dbname=mydb',
                'user' => 'root',
                'password' => '',
            ],
        ];
        $app = new Application($config);
        $service = new PdoService();

        $pdo = $service($app);
        $this->assertInstanceOf('PDO', $pdo);
        $this->assertEquals(PDO::ERRMODE_WARNING, $pdo->getAttribute(PDO::ATTR_ERRMODE));
    }
}
