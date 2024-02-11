<?php
namespace AugmentedSteam\Server\Lib\Redis;

class RedisClient extends \Predis\Client{

    public function __construct(RedisConfig $config) {
        parent::__construct([
            "scheme" => $config->getScheme(),
            "host" => $config->getHost(),
            "port" => $config->getPort(),
            "database" => $config->getDatabase()
        ], [
            "prefix" => $config->getPrefix(),
        ]);
    }
}
