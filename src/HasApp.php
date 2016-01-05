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
     * Injects an application instance.
     *
     * @param Application $app container
     *
     * @return self
     */
    public function injectApp(Application $app)
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
        return $this->app;
    }
}
