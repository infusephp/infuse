<?php

namespace App\Services;

use Pulsar\Model;
use Pulsar\Validate;

class ModelDriver
{
    /**
     * @var Pulsar\Driver\DriverInterface
     */
    private $driver;

    public function __construct($app)
    {
        $config = $app['config'];

        // make the app available to models
        Model::inject($app);

        // set up the model driver
        $class = $config->get('models.driver');
        $this->driver = new $class($app);
        Model::setDriver($this->driver);

        // used for password hasing
        Validate::configure(['salt' => $config->get('site.salt')]);
    }

    public function __invoke()
    {
        return $this->driver;
    }
}
