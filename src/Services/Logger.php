<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
namespace Infuse\Services;

use Monolog\ErrorHandler;
use Monolog\Handler\NullHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Logger as Monolog;

class Logger
{
    /**
     * @var Monolog\Logger
     */
    private $logger;

    public function __construct($app)
    {
        // Install the PHP error handler now only if logging
        // is enabled. Otherwise we don't need to instantiate the
        // logger until it has been requested through the DI container.
        if ($this->hasLogging($app)) {
            ErrorHandler::register($this->getLogger($app));
        }
    }

    public function __invoke($app)
    {
        return $this->getLogger($app);
    }

    private function hasLogging($app)
    {
        return $app['config']->get('logger.enabled');
    }

    private function getLogger($app)
    {
        if (!$this->logger) {
            $handlers = [];
            $config = $app['config'];

            if ($this->hasLogging($app)) {
                $webProcessor = new WebProcessor();
                $webProcessor->addExtraField('user_agent', 'HTTP_USER_AGENT');

                $processors = [
                    $webProcessor,
                    new IntrospectionProcessor(),
                ];
            } else {
                $processors = [];
                $handlers[] = new NullHandler();
            }

            $this->logger = new Monolog($config->get('site.hostname'), $handlers, $processors);
        }

        return $this->logger;
    }
}
