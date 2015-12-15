<?php

namespace App\Services;

use Monolog\ErrorHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\FirePHPHandler;
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
        $config = $app['config'];
        $handlers = [];
        $loggingEnabled = $config->get('logger.enabled');

        if ($loggingEnabled) {
            $webProcessor = new WebProcessor();
            $webProcessor->addExtraField('user_agent', 'HTTP_USER_AGENT');

            $processors = [
                $webProcessor,
                new IntrospectionProcessor(), ];

            // firephp
            if (!$config->get('site.production-level')) {
                $handlers[] = new FirePHPHandler();
            }
        } else {
            $processors = [];
            $handlers[] = new NullHandler();
        }

        $this->logger = new Monolog($config->get('site.hostname'), $handlers, $processors);

        if ($loggingEnabled) {
            ErrorHandler::register($this->logger);
        }
    }

    public function __invoke()
    {
        return $this->logger;
    }
}
