<?php

namespace App\Services;

use Infuse\View;

class ViewEngine
{
    public function __construct($app)
    {
        View::inject($app);
    }

    public function __invoke($app)
    {
        $config = $app['config'];
        $class = $config->get('views.engine');

        // use PHP view engine by default
        if (!$class) {
            $class = 'Infuse\ViewEngine\PHP';
        }

        // Smarty needs special parameters
        if ($class === 'Infuse\ViewEngine\Smarty') {
            $engine = new $class(INFUSE_VIEWS_DIR, INFUSE_TEMP_DIR.'/smarty', INFUSE_TEMP_DIR.'/smarty/cache');
        } else {
            $engine = new $class(INFUSE_VIEWS_DIR);
        }

        // static assets
        $engine->setAssetMapFile(INFUSE_ASSETS_DIR.'/static.assets.json')
               ->setAssetBaseUrl($config->get('assets.base_url'))
               ->setGlobalParameters(['app' => $app]);

        return $engine;
    }
}
