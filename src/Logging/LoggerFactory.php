<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\Handler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;

class LoggerFactory implements LoggerFactoryInterface
{
    public function __construct(
        private readonly LoggingConfig $config,
        private readonly string $logsPath
    ) {}

    private function getLineFormatter(): LineFormatter {
        return (new LineFormatter("[%extra.uid%][%datetime%]%extra.prefix% %level_name%: %message% %context% %extra%\n", "Y-m-d H:i:s.u"))
            ->ignoreEmptyContextAndExtra();
    }

    private function getFileHandler(string $channel): Handler {
        $handler = new RotatingFileHandler(
            $this->logsPath."/{$channel}.log",
            $this->config->getMaxFiles(),
            Logger::DEBUG,
            true,
            0666
        );
        $handler->setFilenameFormat("{date}.{filename}", "Y-m-d");
        $handler->setFormatter($this->getLineFormatter());
        return $handler;
    }

    public function getNullLogger(): Logger {
        return new Logger("null", [new NullHandler()]);
    }

    public function create(string $channel): Logger {
        if (!$this->config->isEnabled()) {
            return $this->getNullLogger();
        }

        return (new Logger($channel))
            ->pushHandler($this->getFileHandler($channel))
            ->pushProcessor(new UidProcessor())
            // TODO ->pushProcessor(new WebProcessor())
            ;
    }
}
