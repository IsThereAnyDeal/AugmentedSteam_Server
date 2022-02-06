<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

class BrightDataConfig extends AConfig
{
    protected function getSchema(): Schema {
        return Expect::structure([
            "url" => Expect::string()->required(),
            "port" => Expect::int()->required(),
            "user" => Expect::string()->required(),
            "password" => Expect::string()->required(),
            "zone" => Expect::string()->required()
        ]);
    }

    public function getUrl(): string {
        return $this->config->url;
    }

    public function getPort(): int {
        return $this->config->port;
    }

    public function getUser(): string {
        return $this->config->user;
    }

    public function getPassword(): string {
        return $this->config->password;
    }

    public function getZone(): string {
        return $this->config->zone;
    }
}
