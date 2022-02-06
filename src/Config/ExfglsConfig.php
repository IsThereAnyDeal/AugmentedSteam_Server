<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

class ExfglsConfig extends AConfig
{
    protected function getSchema(): Schema {
        return Expect::structure([
            "enabled" => Expect::bool(false),
            "bin" => Expect::string(),
            "user" => Expect::string(),
            "password" => Expect::string(),
        ]);
    }

    public function isEnabled(): bool {
        return $this->config->enabled;
    }

    public function getBin(): string {
        return $this->config->bin;
    }

    public function getUser(): string {
        return $this->config->user;
    }

    public function getPassword(): string {
        return $this->config->password;
    }
}
