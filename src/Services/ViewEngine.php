<?php

namespace Infuse\Services;

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

        // get the view-related directories
        $assetsDir = $config->get('dirs.assets');
        $tempDir = $config->get('dirs.temp');
        $viewsDir = $config->get('dirs.views');

        // use PHP view engine by default
        $class = $config->get('views.engine');
        if (!$class) {
            $class = 'Infuse\ViewEngine\PHP';
        }

        // Smarty needs special parameters
        if ($class === 'Infuse\ViewEngine\Smarty') {
            $smartyTemp = "$tempDir/smarty";
            $engine = new $class($viewsDir, $smartyTemp, "$smartyTemp/cache");
        } else {
            $engine = new $class($viewsDir);
        }

        // static assets
        $assetsUrl = $config->get('assets.base_url');
        $engine->setAssetMapFile("$assetsDir/static.assets.json")
               ->setAssetBaseUrl($assetsUrl)
               ->setGlobalParameters(['app' => $app]);

        return $engine;
    }
}
