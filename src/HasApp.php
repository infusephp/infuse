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
    /**
     * @var Application
     */
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
     * Gets the application instance.
     *
     * @return Application
     */
    public function getApp()
    {
        if (!$this->app) {
            return Application::getDefault();
        }

        return $this->app;
    }
}
