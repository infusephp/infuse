<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
namespace Infuse\Console;

use Infuse\Application as App;

class Application extends \Symfony\Component\Console\Application
{
    public function __construct(App $app)
    {
        parent::__construct();

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
