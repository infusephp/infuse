<?php

namespace Test;

use Infuse\Request;
use Infuse\Response;

class TestController
{
    public static $called = false;
    public static $appInjected = false;

    public function setApp($app)
    {
        self::$appInjected = true;
    }

    public function route(Request $req, Response $res, array $args)
    {
        self::$called = true;
    }
}
