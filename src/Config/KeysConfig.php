<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

class KeysConfig extends AConfig
{
    protected function getSchema(): Schema {
        return Expect::structure([
            "itad" => Expect::string()->required(),
            "steampeek" => Expect::string()->required()
        ]);
    }

    public function getIsThereAnyDealApiKey(): string {
        return $this->config->itad;
    }

    public function getSteamPeekApiKey(): string {
        return $this->config->steampeek;
    }
}
