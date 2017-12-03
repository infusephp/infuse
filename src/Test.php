<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */

namespace Infuse;

use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test as PHPUnitTest;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;

class Test implements TestListener
{
    /**
     * @var Application
     */
    public static $app;

    public function __construct()
    {
        // display all errors, log none
        ini_set('display_errors', 1);
        ini_set('log_errors', 0);
        error_reporting(E_ALL | E_STRICT);

        $config = [];
        if (file_exists('config.php')) {
            $config = include 'config.php';
        }

        self::$app = new Application($config, Application::ENV_TEST);
    }

    public function addError(PHPUnitTest $test, Exception $e, $time)
    {
    }

    public function addFailure(PHPUnitTest $test, AssertionFailedError $e, $time)
    {
    }

    public function addIncompleteTest(PHPUnitTest $test, Exception $e, $time)
    {
    }

    public function addRiskyTest(PHPUnitTest $test, Exception $e, $time)
    {
    }

    public function addSkippedTest(PHPUnitTest $test, Exception $e, $time)
    {
    }

    public function addWarning(PHPUnitTest $test, Warning $e, $time)
    {
    }

    public function startTest(PHPUnitTest $test)
    {
    }

    public function endTest(PHPUnitTest $test, $time)
    {
    }

    public function startTestSuite(TestSuite $suite)
    {
        printf("\n\n%s:\n", $suite->getName());
    }

    public function endTestSuite(TestSuite $suite)
    {
    }
}
