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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OptimizeCommand extends Command
{
    use \InjectApp;

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

        $config = "<?php\n// THIS FILE IS AUTO-GENERATED\n";
        $config .= 'return ';
        $config .= var_export($this->app['config']->all());

        $configFile = INFUSE_BASE_DIR.'/config.php';
        if (file_put_contents($configFile, $config)) {
            $output->writeln('Success!');
        } else {
            $output->writeln('Could not write config.php');
        }
    }
}
