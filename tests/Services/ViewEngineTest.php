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
use Infuse\Services\ViewEngine;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ViewEngineTest extends MockeryTestCase
{
    public function testInvoke()
    {
        $config = [
            'assets' => [
                'base_url' => 'http://example.com',
            ],
        ];
        $app = new Application($config);
        $service = new ViewEngine($app);
        $engine = $service($app);

        $this->assertInstanceOf('Infuse\ViewEngine\PHP', $engine);

        $this->assertEquals(['app' => $app], $engine->getGlobalParameters());
        $this->assertEquals('http://example.com/img/logo.png', $engine->asset_url('/img/logo.png'));
        $this->assertEquals(INFUSE_BASE_DIR.'/views', $engine->getViewsDir());
    }

    public function testInvokeSmarty()
    {
        $config = [
            'views' => [
                'engine' => 'Infuse\ViewEngine\Smarty',
            ],
        ];

        $app = new Application($config);
        $service = new ViewEngine($app);
        $engine = $service($app);

        $this->assertInstanceOf('Infuse\ViewEngine\Smarty', $engine);
    }
}
