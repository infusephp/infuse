<?php

namespace App\Services;

class Locale
{
    public function __invoke($app)
    {
        $locale = new \Infuse\Locale($app['config']->get('site.language'));
        $locale->setLocaleDataDir(INFUSE_ASSETS_DIR.'/locales');

        return $locale;
    }
}
