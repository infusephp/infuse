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

trait HasApp
{
    protected $app;

    /**
     * Sets the application instance.
     *
     * @param Application $app container
     *
     * @return self
     */
    public function setApp(Application $app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * @deprecated
     */
    public function injectApp(Application $app)
    {
        return $this->setApp($app);
    }

    /**
     * Gets the application instance.
     *
     * @return Application
     */
    public function getApp()
    {
        return $this->app;
    }
}
