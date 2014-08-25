<?php

namespace App\Console;

use App;

use infuse\Session;
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
        $result = $this->migrate( $input->getArgument( 'module' ), $output );

        return $result ? 0 : 1;
    }

    /**
     * Runs migrations for all app modules or a specified module.
     * Also, will setup database sessions if enabled
     *
     * @param string $module optional module
     * @param OutputInterface $output
     *
     * @return boolean success
     */
    private function migrate( $module = '', OutputInterface $output )
    {
        $success = true;

        $output->writeln( '-- Running migrations' );

        // database sessions
        if( $this->app[ 'config' ]->get( 'sessions.adapter' ) == 'database' )
        {
            $output->writeln( 'Migrating Database Sessions' );

            $result = Session\Database::install();

            if( $result )
                $output->writeln( '-- Database Sessions Installed' );
            else
                $output->writeln( '-- Error installing Database Sessions' );

            $success = $result && $success;
        }

        // module migrations
        $modules = (empty($module)) ? $this->app[ 'config' ]->get( 'modules.all' ) : [ $module ];

        foreach( (array)$modules as $mod )
        {
            $output->writeln( "-- Migrating $mod" );

            $result = 1;
            putenv( "PHINX_APP_MODULE=$mod" );

            ob_start();
            system( 'php vendor/robmorgan/phinx/bin/phinx migrate', $result );
            $output = ob_get_contents();
            ob_end_clean();

            $success = $result && $success;

            $lines = explode( "\n", $output );

            // clean up the output
            foreach( $lines as $line )
            {
                if( !empty( $line ) && substr( $line, 0, 3 ) == ' ==' )
                    $output->writeln( $line );
            }
        }

        if( $success )
            $output->writeln( '-- Success!' );
        else
            $output->writeln( '-- Error running migrations' );

        return $success;
    }
}