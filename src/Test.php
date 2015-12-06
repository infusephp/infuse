<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
use App\Users\Models\User;

class Test implements PHPUnit_Framework_TestListener
{
    /**
     * @var App
     */
    public static $app;

    /**
     * @var string
     */
    public static $userEmail;

    /**
     * @var string
     */
    public static $userPassword = 'testpassword';

    private $verbose;

    public function __construct($verbose = false)
    {
        $config = @include 'config.php';
        if (!$config) {
            $config = [];
        }

        $config['logger']['enabled'] = $verbose;

        self::$app = new App($config);

        // execute middleware
        self::$app->executeMiddleware();

        $this->verbose = $verbose;

        /* Create a test user and sign in */
        if (class_exists('App\Users\Models\User')) {
            if ($this->verbose) {
                echo "Logging in a test user to run the test suite.\n";
            }

            self::$userEmail = 'test@exmaple.com';

            $user = new User();
            if (property_exists($user, 'testUser')) {
                $testInfo = $user::$testUser;
            } else {
                $testInfo = [
                    'first_name' => 'Bob',
                    'ip' => '127.0.0.1', ];
            }

            $testInfo['user_email'] = self::$userEmail;
            $testInfo['user_password'] = [self::$userPassword, self::$userPassword];

            $existingUser = User::where('user_email', $testInfo['user_email'])
                ->first();
            if ($existingUser) {
                $existingUser->grantAllPermissions();
                $existingUser->delete();
            }

            $success = $user->create($testInfo);

            if ($this->verbose) {
                if ($success) {
                    echo 'User #'.$user->id()." created.\n";
                } else {
                    echo "Could not create test user.\n";
                }
            }

            $loggedIn = self::$app['auth']->login(self::$userEmail, self::$userPassword);

            if ($this->verbose) {
                if ($loggedIn) {
                    echo 'User #'.self::$app['user']->id()." logged in.\n";
                } else {
                    echo " Could not log test user in.\n";
                }
            }
        }

        // TODO custom listeners should be used instead
        self::$app['config']->set('email.type', 'nop');
    }

    public function __destruct()
    {
        if (isset(self::$app['user'])) {
            $user = self::$app['user'];
            $user->grantAllPermissions();
            $deleted = $user->delete();

            if ($this->verbose) {
                if ($deleted) {
                    echo 'User #'.$user->id()." deleted.\n";
                } else {
                    echo 'User #'.$user->id()." NOT deleted.\n";
                }
            }
        }
    }

    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($this->verbose) {
            printf(" Error while running test '%s'.\n", $test->getName());
        }
    }

    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        if ($this->verbose) {
            printf("Test '%s' failed.\n", $test->getName());
        }
    }

    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($this->verbose) {
            printf("Test '%s' is incomplete.\n", $test->getName());
        }
    }

    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($this->verbose) {
            printf("Test '%s' is deemed risky.\n", $test->getName());
        }
    }

    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($this->verbose) {
            printf("Test '%s' has been skipped.\n", $test->getName());
        }
    }

    public function startTest(PHPUnit_Framework_Test $test)
    {
        if ($this->verbose) {
            printf("Test '%s' started.\n", $test->getName());
        }

        if (class_exists('App\Users\Models\User')) {
            self::$app['user']->disableSU();
        }
    }

    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($this->verbose) {
            printf("Test '%s' ended.\n", $test->getName());
        }
    }

    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if ($this->verbose) {
            printf("TestSuite '%s' started.\n", $suite->getName());
        } elseif ($suite->getName() != 'App') {
            printf("\n\n%s:\n", $suite->getName());
        }
    }

    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if ($this->verbose) {
            printf("TestSuite '%s' ended.\n", $suite->getName());
        }

        // nuke memcache in between test suites
        if (self::$app['config']->get('memcache.enabled')) {
            self::$app['memcache']->flush();
        }

        $errors = self::$app['errors']->errors();

        if (count($errors) > 0) {
            if ($this->verbose) {
                printf("TestSuite '%s' produced these errors:\n", $suite->getName());
                print_r($errors);
            }
            self::$app['errors']->clear();
        }
    }
}
