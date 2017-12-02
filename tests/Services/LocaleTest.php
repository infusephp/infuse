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
use Infuse\Services\Locale;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class LocaleTest extends MockeryTestCase
{
    public function testInvoke()
    {
        $config = [
            'i18n' => [
                'locale' => 'en-us',
            ],
        ];

        $app = new Application($config);
        $service = new Locale();
        $locale = $service($app);

        $this->assertInstanceOf('Infuse\Locale', $locale);
        $this->assertEquals('en-us', $locale->getLocale());
    }
}
