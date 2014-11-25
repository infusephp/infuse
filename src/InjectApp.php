<?php

/**
 * @package idealistsoft\framework-bootstrap
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2014 Jared King
 * @license MIT
 */

trait InjectApp
{
    protected $app;

    public function injectApp(App $app)
    {
        $this->app = $app;
    }
}
