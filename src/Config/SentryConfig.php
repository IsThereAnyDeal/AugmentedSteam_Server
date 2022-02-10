<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

class SentryConfig extends AConfig
{
    protected function getSchema(): Schema {
        return Expect::structure([
            "enabled" => Expect::bool(false)->required(),
            "dsn" => Expect::string(),
        ]);
    }

    public function isEnabled(): bool {
        return $this->config->enabled;
    }

    public function getDsn(): string {
        return $this->config->dsn;
    }
}
