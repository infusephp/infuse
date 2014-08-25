<?php

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RouteCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName( 'route' )
            ->setDescription( 'Call a route on the app' )
            ->addArgument(
                'route',
                InputArgument::REQUIRED,
                'Route to pass to app'
            );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $result = 1;
        system( "php public/index.php " . $input->getArgument( 'route' ), $result );
        return $result;
    }
}