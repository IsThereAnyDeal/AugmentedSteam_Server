<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

class CoreConfig extends AConfig
{
    protected function getSchema(): Schema {
        return Expect::structure([
            "env" => Expect::anyOf("prod", "dev")->default("prod"),
            "errors" => Expect::bool(false),
        ]);
    }

    public function isProduction(): bool {
        return $this->config->env === "prod";
    }

    public function isShowErrors(): bool {
        return $this->config->errors;
    }
}
