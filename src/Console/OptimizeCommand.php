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

use Infuse\HasApp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OptimizeCommand extends Command
{
    use HasApp;

    protected function configure()
    {
        $this
            ->setName('optimize')
            ->setDescription('Optimizes the app');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Optimizing app');

        if ($this->app['config']->get('router.cacheFile')) {
            $this->cacheRouteTable($output);
        } else {
            $output->writeln('The route table could be cached with the router.cacheFile setting');
        }

        $this->cacheConfig($output);

        return 0;
    }

    private function cacheRouteTable(OutputInterface $output)
    {
        $output->writeln('-- Caching route table');

        $cacheFile = $this->app['config']->get('router.cacheFile');
        @unlink($cacheFile);

        $this->app['router']->getDispatcher();

        $output->writeln('Success!');
    }

    private function cacheConfig(OutputInterface $output)
    {
        $output->writeln('-- Caching configuration');

        // build a hard-coded version of the application configuration
        // that does not include any configuration building logic
        $config = "<?php\n// THIS FILE IS AUTO-GENERATED\n";
        $config .= 'return ';
        $config .= var_export($this->app['config']->all(), true);
        $config .= ';';

        $configFile = INFUSE_BASE_DIR.'/config.php';
        $originalFile = INFUSE_BASE_DIR.'/config.original.php';
        // move the original config file to a backup
        if (file_exists($configFile) && !file_exists($originalFile)) {
            rename($configFile, $originalFile);
        }

        // flush out 
        if (file_put_contents($configFile, $config)) {
            $output->writeln('Success!');
        } else {
            $output->writeln('Could not write config.php');
        }
    }
}
