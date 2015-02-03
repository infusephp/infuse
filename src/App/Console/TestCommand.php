<?php

/**
 * @package infuse\bootstrap
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license MIT
 */

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('test')
            ->setDescription('Run app tests')
            ->addArgument(
                'module',
                InputArgument::OPTIONAL,
                'Specific module to run tests for'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $args = [];
        if ($module = $input->getArgument('module')) {
            $args[] = 'app/'.$module.'/tests/';
        }

        $result = 1;
        system('phpunit '.implode(' ', $args), $result);

        return $result;
    }
}
