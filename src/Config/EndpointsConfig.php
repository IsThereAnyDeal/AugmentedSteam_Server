<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

class EndpointsConfig extends AConfig
{
    protected function getSchema(): Schema {
        return Expect::structure([
            "wsgf" => Expect::string()->required(),
            "keylol" => Expect::string()->required(),
            "steamspy" => Expect::string()->required(),
            "steamtools" => Expect::string()->required(),
            "steamrep" => Expect::string()->required(),
            "pcgw" => Expect::string()->required(),
            "steampeek" => Expect::string()->required(),
        ]);
    }

    public function getWSGFEndpoint(): string {
        return $this->config->wsgf;
    }

    public function getKeyLolEndpoint(): string {
        return $this->config->keylol;
    }

    public function getSteamSpyEndpoint(): string {
        return $this->config->steamspy;
    }

    public function getSteamToolsEndpoint(): string {
        return $this->config->steamtools;
    }

    public function getSteamRepEndpoint(): string {
        return $this->config->steamrep;
    }

    public function getPCGWEndpoint(): string {
        return $this->config->pcgw;
    }

    public function getSteamPeekEndpoint(): string {
        return $this->config->steampeek;
    }
}
