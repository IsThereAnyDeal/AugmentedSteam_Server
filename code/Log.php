<?php
class Log {
    private static $loggers = [];

    private static function addFileHandler(\Monolog\Logger $logger, $channel) {
        $bubble = true;
        $path = __DIR__ . "/../" .trim(Config::LogsPath, "/")."/".$channel.".log";

        $handler = new \Monolog\Handler\RotatingFileHandler($path, Config::LogsMaxFiles, \Monolog\Logger::DEBUG, $bubble);
        $handler->setFilenameFormat("{date}.{filename}", "Y-m-d");
        $logger->pushHandler($handler);
    }

    /**
     * @param string $channel
     * @param bool $isApi
     * @return \Monolog\Logger
     */
    public static function channel(string $channel, bool $isApi=false): \Monolog\Logger {
        if (!isset($loggers[$channel])) {
            $logger = new \Monolog\Logger($channel);

            if (Config::LogsEnabled) {
                if ($isApi) {
                    $logger->pushProcessor(new \Monolog\Processor\WebProcessor());
                }

                self::addFileHandler($logger, $channel);

            } else {
                $logger->pushHandler(new \Monolog\Handler\NullHandler());
            }

            self::$loggers[$channel] = $logger;
        }

        return self::$loggers[$channel];
    }
}
