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

class Locale
{
    public function __invoke($app)
    {
        $config = $app['config'];
        $assetsDir = $config->get('dirs.assets');

        $locale = new \Infuse\Locale($config->get('site.language'));
        $locale->setLocaleDataDir("$assetsDir/locales");

        return $locale;
    }
}
