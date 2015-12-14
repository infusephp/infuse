<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
namespace App\Console;

use App;
use Infuse\Request;
use Infuse\Response;

class Application extends \Symfony\Component\Console\Application
{
    public function __construct(App $app)
    {
        parent::__construct();

        // run middleware
        $req = new Request();
        $res = new Response();
        $app->executeMiddleware($req, $res);

        // add app-specific commands
        $commands = (array) $app['config']->get('modules.commands');

        foreach ($commands as $class) {
            $this->addCommand($class, $app);
        }
    }

    private function addCommand($class, $app)
    {
        $command = new $class();
        if (method_exists($class, 'injectApp')) {
            $command->injectApp($app);
        }

        $this->add($command);
    }
}
