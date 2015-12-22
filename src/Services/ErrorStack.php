<?php

namespace Infuse\Services;

class ErrorStack
{
    public function __invoke($app)
    {
        return new \Infuse\ErrorStack($app);
    }
}
