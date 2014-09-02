<?php

namespace App\Console;

use App;

class Application extends \Symfony\Component\Console\Application
{
    public function __construct(App $app)
    {
        parent::__construct();

        // add commands
        $this->add( new MigrateCommand( $app ) );
        $this->add( new TestCommand() );
        $this->add( new RouteCommand() );
    }
}
