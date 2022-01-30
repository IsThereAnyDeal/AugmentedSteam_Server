<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

class LoggingConfig extends AConfig
{
    protected function getSchema(): Schema {
        return Expect::structure([
            "enabled" => Expect::bool(true),
            "path" => Expect::string("logs/"),
            "maxFiles" => Expect::int(14)
        ]);
    }

    public function isEnabled(): bool {
        return $this->config->enabled;
    }

    public function getPath(): string {
        return $this->config->path;
    }

    public function getMaxFiles(): int {
        return $this->config->maxFiles;
    }
}
