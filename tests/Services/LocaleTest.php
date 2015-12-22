<?php

use Infuse\Application;
use Infuse\Services\Locale;

class LocaleTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $app = new Application();
        $service = new Locale();
        $locale = $service($app);

        $this->assertInstanceOf('Infuse\Locale', $locale);
        $this->assertEquals('en', $locale->getLocale());
    }
}
