<?php

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
                'hostname' => 'localhost' ],
            'assets' => [
                'dirs' => [
                    '/public' ]]];

        $config = $app['config'];
        $this->assertInstanceOf('\\infuse\\Config', $config);
        $this->assertEquals($expected, $config->get());
    }

    public function testLogger()
    {
        $app = new App(['logger' => ['enabled' => true]]);

        $this->assertInstanceOf('\\Monolog\\Logger', $app['logger']);
    }

    public function testLocale()
    {
        $app = new App([
            'site' => [
                'language' => 'french' ]]);

        $locale = $app['locale'];
        $this->assertInstanceOf('\\infuse\\Locale', $locale);
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
                'password' => '' ]]);

        $db = $app['db'];
        $this->assertInstanceOf('\\infuse\\QueryBuilder', $db);

        $pdo = $db->getPDO();
        $this->assertInstanceOf('\\PDO', $pdo);
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));

        $app = new App([
            'site' => [
                'production-level' => true ],
            'database' => [
                'dsn' => 'mysql:host=localhost;dbname=mydb',
                'user' => 'root',
                'password' => '' ]]);

        $db = $app['db'];
        $this->assertInstanceOf('\\infuse\\QueryBuilder', $db);
        $pdo = $db->getPDO();
        $this->assertInstanceOf('\\PDO', $pdo);
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

        $this->assertInstanceOf('\\infuse\\Request', $app['req']);
    }

    public function testResponse()
    {
        $app = new App();

        $this->assertInstanceOf('\\infuse\\Response', $app['res']);
    }

    public function testQueue()
    {
        $app = new App();

        $this->assertInstanceOf('\\infuse\\Queue', $app['queue']);
    }

    public function testErrorStack()
    {
        $app = new App();

        $this->assertInstanceOf('\\infuse\\ErrorStack', $app['errors']);
    }

    public function testViewEngine()
    {
        $app = new App();

        $this->assertInstanceOf('\\infuse\\ViewEngine\\PHP', $app['view_engine']);

        $app = new App(['views' => ['engine' => 'smarty']]);

        $this->assertInstanceOf('\\infuse\\ViewEngine\\Smarty', $app['view_engine']);

        // invalid engine
        $thrown = false;
        try {
            $app = new App(['views' => ['engine' => 'whatever']]);
            $engine = $app['view_engine'];
        } catch (Exception $e) {
            $thrown = true;
        }

        $this->assertTrue($thrown);
    }

    public function testMiddleware()
    {
        $this->markTestIncomplete();
    }

    public function testGo()
    {
        $this->markTestIncomplete();
    }

    public function testGet()
    {
        $this->markTestIncomplete();
    }

    public function testPost()
    {
        $this->markTestIncomplete();
    }

    public function testPut()
    {
        $this->markTestIncomplete();
    }

    public function testDelete()
    {
        $this->markTestIncomplete();
    }

    public function testPatch()
    {
        $this->markTestIncomplete();
    }

    public function testOptions()
    {
        $this->markTestIncomplete();
    }

    public function testMap()
    {
        $this->markTestIncomplete();
    }
}