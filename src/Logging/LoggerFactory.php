<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;

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

    private function getFileHandler(string $channel): StreamHandler {
        $date = date("Y-m-d");
        $logPath = $this->logsPath."/{$date}.{$channel}.log";
        return (new StreamHandler($logPath, Level::Debug, true, 0666));
    }

    public function getNullLogger(): LoggerInterface {
        return new Logger("null", [new NullHandler()]);
    }

    public function logger(string $channel): LoggerInterface {
        if (!$this->config->isEnabled()) {
            return $this->getNullLogger();
        }

        $lineFormatter = $this->getLineFormatter();

        $fileHandler = $this->getFileHandler($channel);
        $fileHandler->setFormatter($lineFormatter);

        return (new Logger($channel))
            ->pushHandler($this->getFileHandler($channel))
            ->pushProcessor(new UidProcessor());
    }

    public function access(): LoggerInterface {
        if (!$this->config->isEnabled()) {
            return $this->getNullLogger();
        }

        $lineFormatter = $this->getLineFormatter();

        $channel = "access";
        $fileHandler = $this->getFileHandler($channel);
        $fileHandler->setFormatter($lineFormatter);

        return (new Logger($channel))
            ->pushHandler($this->getFileHandler($channel))
            ->pushProcessor(new UidProcessor())
            ->pushProcessor(new WebProcessor(extraFields: ["ip", "server", "referrer"]));
    }
}
