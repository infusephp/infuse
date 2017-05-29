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

        // Instantiate the engine, and pass in any special configuration
        if ($class === 'Infuse\ViewEngine\Smarty') {
            $smartyTemp = "$tempDir/smarty";
            $engine = new $class($viewsDir, $smartyTemp, "$smartyTemp/cache");
        } else if ($class == 'Infuse\ViewEngine\Twig') {
            $twigTemp = "$tempDir/twig";
            $twigParams = (array) $config->get('views.twigConfig');
            $twigParams = array_replace(['cache' => $twigTemp], $twigParams);
            $engine = new $class($viewsDir, $twigParams);
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
