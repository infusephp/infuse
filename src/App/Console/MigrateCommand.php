<?php

namespace App\Console;

use App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    private $app;

    function __construct( App $app )
    {
        parent::__construct();

        $this->app = $app;
    }

    protected function configure()
    {
        $this
            ->setName( 'migrate' )
            ->setDescription( 'Run app migrations' )
            ->addArgument(
                'module',
                InputArgument::OPTIONAL,
                'Specific module to run migrations for'
            );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $result = $this->app->migrate( $input->getArgument( 'module' ), true );

        return $result ? 0 : 1;
    }
}