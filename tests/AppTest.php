<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
class AppTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        date_default_timezone_set('UTC');
    }

    public function testConfig()
    {
        $app = new App(['test' => true]);

        $expected = [
            'test' => true,
            'site' => [
                'hostname' => 'localhost', ],
            'assets' => [
                'dirs' => [
                    '/public', ], ], ];

        $config = $app['config'];
        $this->assertInstanceOf('Infuse\Config', $config);
        $this->assertEquals($expected, $config->get());
    }

    public function testLogger()
    {
        $app = new App(['logger' => ['enabled' => true]]);

        $this->assertInstanceOf('Monolog\Logger', $app['logger']);
    }

    public function testLocale()
    {
        $app = new App([
            'site' => [
                'language' => 'french', ], ]);

        $locale = $app['locale'];
        $this->assertInstanceOf('Infuse\Locale', $locale);
        $this->assertEquals('french', $locale->getLocale());
    }

    public function testDatabase()
    {
        $app = new App([
            'database' => [
                'type' => 'mysql',
                'host' => 'localhost',
                'name' => 'mydb',
                'user' => 'root',
                'password' => '', ], ]);

        $db = $app['db'];
        $this->assertInstanceOf('JAQB\QueryBuilder', $db);

        $pdo = $db->getPDO();
        $this->assertInstanceOf('PDO', $pdo);
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));

        $app = new App([
            'site' => [
                'production-level' => true, ],
            'database' => [
                'dsn' => 'mysql:host=localhost;dbname=mydb',
                'user' => 'root',
                'password' => '', ], ]);

        $db = $app['db'];
        $this->assertInstanceOf('JAQB\QueryBuilder', $db);
        $pdo = $db->getPDO();
        $this->assertInstanceOf('PDO', $pdo);
        $this->assertEquals(PDO::ERRMODE_WARNING, $pdo->getAttribute(PDO::ATTR_ERRMODE));
    }

    public function testRedis()
    {
        $this->markTestIncomplete();
    }

    public function testMemcache()
    {
        $this->markTestIncomplete();
    }

    public function testReq()
    {
        $app = new App();

        $this->assertInstanceOf('Infuse\Request', $app['req']);
    }

    public function testResponse()
    {
        $app = new App();

        $this->assertInstanceOf('Infuse\Response', $app['res']);
    }

    public function testQueue()
    {
        $app = new App(['queue' => [
            'driver' => 'Infuse\Queue\Driver\SynchronousDriver', ]]);

        $this->assertInstanceOf('Infuse\Queue\Driver\SynchronousDriver', Queue::getDriver());
    }

    public function testModel()
    {
        $app = new App(['models' => [
            'driver' => 'Infuse\Model\Driver\DatabaseDriver',
            'cache_ttl' => 30, ]]);

        $this->assertInstanceOf('Infuse\Model\Driver\DatabaseDriver', Model::getDriver());
    }

    public function testErrorStack()
    {
        $app = new App();

        $this->assertInstanceOf('Infuse\ErrorStack', $app['errors']);
    }

    public function testViewEngine()
    {
        $app = new App();
        $this->assertInstanceOf('Infuse\ViewEngine\PHP', $app['view_engine']);

        $app = new App(['views' => [
            'engine' => 'Infuse\ViewEngine\Smarty', ]]);
        $this->assertInstanceOf('Infuse\ViewEngine\Smarty', $app['view_engine']);
    }

    public function testRouter()
    {
        $app = new App();

        $this->assertInstanceOf('Infuse\Router', $app['router']);
    }

    public function testRoutes()
    {
        $config = ['routes' => ['test']];
        $app = new App($config);

        $this->assertEquals(['test'], $app->getRoutes());
    }

    public function testMiddleware()
    {
        $config = ['modules' => ['middleware' => ['test']]];
        $app = new App($config);

        $this->assertEquals(['test'], $app->getMiddleware());
    }

    public function testGo()
    {
        $this->markTestIncomplete();
    }

    public function testGet()
    {
        $app = new App();
        $handler = function () {};

        $this->assertEquals($app, $app->get('/users/{id}', $handler));

        $this->assertEquals(['get /users/{id}' => $handler], $app->getRoutes());
    }

    public function testPost()
    {
        $app = new App();
        $handler = function () {};

        $this->assertEquals($app, $app->post('/users', $handler));

        $this->assertEquals(['post /users' => $handler], $app->getRoutes());
    }

    public function testPut()
    {
        $app = new App();
        $handler = function () {};

        $this->assertEquals($app, $app->put('/users/{id}', $handler));

        $this->assertEquals(['put /users/{id}' => $handler], $app->getRoutes());
    }

    public function testDelete()
    {
        $app = new App();
        $handler = function () {};

        $this->assertEquals($app, $app->delete('/users/{id}', $handler));

        $this->assertEquals(['delete /users/{id}' => $handler], $app->getRoutes());
    }

    public function testPatch()
    {
        $app = new App();
        $handler = function () {};

        $this->assertEquals($app, $app->patch('/users/{id}', $handler));

        $this->assertEquals(['patch /users/{id}' => $handler], $app->getRoutes());
    }

    public function testOptions()
    {
        $app = new App();
        $handler = function () {};

        $this->assertEquals($app, $app->options('/users/{id}', $handler));

        $this->assertEquals(['options /users/{id}' => $handler], $app->getRoutes());
    }

    public function testMap()
    {
        $app = new App();
        $handler = function () {};

        $this->assertEquals($app, $app->map('GET', '/users/{id}', $handler));

        $this->assertEquals(['get /users/{id}' => $handler], $app->getRoutes());
    }

    public function testGetConsole()
    {
        $app = new App();

        $console = $app->getConsole();
        $this->assertInstanceOf('App\Console\Application', $console);
    }
}
