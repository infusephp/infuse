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
use Infuse\Request;
use Infuse\Response;

class Test implements PHPUnit_Framework_TestListener
{
    /**
     * @var App
     */
    public static $app;

    /**
     * @var string
     */
    public static $userEmail = 'test@example.com';

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

        $config['logger']['enabled'] = false;

        self::$app = new App($config);

        ini_set('display_errors', 1);

        // execute middleware
        $req = new Request();
        $res = new Response();
        self::$app->executeMiddleware($req, $res);

        $this->verbose = $verbose;

        /* Create a test user and sign in */
        if (class_exists('App\Users\Models\User')) {
            if ($this->verbose) {
                echo "Logging in a test user to run the test suite.\n";
            }

            $user = new User();
            $testInfo = [
                'user_email' => self::$userEmail,
                'user_password' => [self::$userPassword, self::$userPassword],
            ];
            if (property_exists($user, 'testUser')) {
                $testInfo = array_replace($testInfo, $user::$testUser);
            } else {
                $testInfo = array_replace($testInfo, [
                    'first_name' => 'Bob',
                    'ip' => '127.0.0.1', ]);
            }

            $existingUser = User::where('user_email', $testInfo['user_email'])
                ->first();
            if ($existingUser) {
                $existingUser->grantAllPermissions()->delete();
            }

            $success = $user->create($testInfo);

            if ($this->verbose) {
                if ($success) {
                    echo 'User #'.$user->id()." created.\n";
                } else {
                    echo "Could not create test user.\n";
                }
            }

            self::$app['user'] = new User($user->id(), true);
        }

        // TODO custom listeners should be used instead
        self::$app['config']->set('email.type', 'nop');
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
