<?php

/**
 * @package idealistsoft\framework-bootstrap
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2014 Jared King
 * @license MIT
 */

namespace App\Console;

use App;

use infuse\Session;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    private $app;

    public function __construct(App $app)
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
            )
            ->addArgument(
                'args',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Optional arguments to pass to phinx'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrateArgs = implode( ' ', $input->getArgument( 'args' ) );
        $result = $this->migrate( $input->getArgument( 'module' ), $migrateArgs, $output );

        return $result ? 0 : 1;
    }

    /**
     * Runs migrations for all app modules or a specified module.
     * Also, will setup database sessions if enabled
     *
     * @param string          $module      optional module
     * @param string          $migrateArgs optional arguments to pass to phinx
     * @param OutputInterface $output
     *
     * @return boolean success
     */
    private function migrate($module = '', $migrateArgs, OutputInterface $output)
    {
        $success = true;

        if( empty( $migrateArgs ) )
            $migrateArgs = 'migrate';

        if( $migrateArgs == 'migrate' )
            $output->writeln( '-- Running migrations' );

        // database sessions
        if ( empty( $module ) && $this->app[ 'config' ]->get( 'sessions.adapter' ) == 'database' ) {
            $output->writeln( '-- Migrating Database Sessions' );

            $result = Session\Database::install();

            if( $result )
                $output->writeln( ' == Database Sessions Installed' );
            else
                $output->writeln(  '== Error installing Database Sessions' );

            $success = $result && $success;
        }

        // module migrations
        $modules = (empty($module)) ? $this->app[ 'config' ]->get( 'modules.all' ) : [ $module ];

        foreach ((array) $modules as $mod) {
            // determine module directory
            $controller = '\\app\\' . $mod . '\\Controller';

            if (class_exists($controller)) {
                $reflection = new \ReflectionClass($controller);
                $migrationPath = dirname($reflection->getFileName()) . '/migrations';

                if (is_dir($migrationPath)) {
                    if ($migrateArgs == 'migrate')
                        $output->writeln("-- Migrating $mod");

                    $success = $this->migrateWithPath($migrationPath, $migrateArgs, $output) && $success;
                }
            }
        }

        if ($migrateArgs == 'migrate') {
            if( $success )
                $output->writeln('-- Success!');
            else
                $output->writeln('-- Error running migrations');
        }

        return $success;
    }

    private function migrateWithPath($path, $migrateArgs, OutputInterface $output)
    {
        $result = 1;
        putenv("PHINX_MIGRATION_PATH=$path");

        ob_start();
        system('php ' . INFUSE_BASE_DIR . '/vendor/bin/phinx ' . $migrateArgs . ' -c ' . INFUSE_BASE_DIR . '/phinx.php', $result);
        $phinxOutput = ob_get_contents();
        ob_end_clean();

        $lines = explode("\n", $phinxOutput);

        // clean up the output
        foreach ($lines as $line) {
            // when migrating, only output lines starting
            // with ' =='
            if ($migrateArgs != 'migrate' ||
                !empty($line) && substr($line, 0, 3) == ' ==')
                $output->writeln($line);
        }

        return $result == 0;
    }
}
