<?php
namespace AugmentedSteam\Server\Lib\Redis;

use Nette\Schema\Expect;
use Nette\Schema\Processor;

class RedisConfig
{
    /**
     * @var object{
     *     scheme: string,
     *     host: string,
     *     port: int,
     *     prefix: string,
     *     database: int
     * }
     */
    private object $data;

    /** @param array<mixed> $config */
    public function __construct(array $config) {
        $this->data = (new Processor())->process( // @phpstan-ignore-line
            Expect::structure([
                "scheme" => Expect::anyOf("tcp")->required(),
                "host" => Expect::string()->required(),
                "port" => Expect::int(6379),
                "prefix" => Expect::string()->required(),
                "database" => Expect::int()->required()
            ]),
            $config
        );
    }

    public function getScheme(): string {
        return $this->data->scheme;
    }

    public function getHost(): string {
        return $this->data->host;
    }

    public function getPort(): int {
        return $this->data->port;
    }

    public function getPrefix(): string {
        return $this->data->prefix;
    }

    public function getDatabase(): int {
        return $this->data->database;
    }
}
