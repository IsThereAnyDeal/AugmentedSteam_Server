<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Logging;

use AugmentedSteam\Server\Config\LoggingConfig;
use Monolog\Handler\Handler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;

class MonologLoggerFactory implements LoggerFactoryInterface
{
    private LoggingConfig $config;

    private array $channels = [];

    public function __construct(LoggingConfig $config) {
        $this->config = $config;
    }

    private function getFileHandler(string $channel): Handler {
        $handler = new RotatingFileHandler(
            __DIR__."/../../".trim($this->config->getPath(), "/")."/{$channel}.log",
            $this->config->getMaxFiles(),
            Logger::DEBUG,
            true,
            0666
        );
        $handler->setFilenameFormat("{date}.{filename}", "Y-m-d");
        return $handler;
    }

    public function getNullLogger(): Logger {
        return new Logger("null", [new NullHandler()]);
    }

    public function createLogger(string $channel): Logger {
        if (!$this->config->isEnabled()) {
            return $this->getNullLogger();
        }

        if (!isset($this->channels[$channel])) {
            $this->channels[$channel] = (new Logger($channel))
                ->pushHandler($this->getFileHandler($channel));
        }
        return $this->channels[$channel];
    }
}
