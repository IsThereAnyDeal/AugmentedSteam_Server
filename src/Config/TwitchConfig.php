<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

class TwitchConfig extends AConfig
{
    protected function getSchema(): Schema {
        return Expect::structure([
            "clientId" => Expect::string()->required(),
            "clientSecret" => Expect::string()->required()
        ]);
    }

    public function getClientId(): string {
        return $this->config->clientId;
    }

    public function getClientSecret(): string {
        return $this->config->clientSecret;
    }
}
