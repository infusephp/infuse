<?php

trait InjectApp
{
	protected $app;

	function injectApp(App $app) {
		$this->app = $app;
	}
}