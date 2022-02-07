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
            "steamcharts" => Expect::string()->required(),
            "steamtools" => Expect::string()->required(),
            "steamrep" => Expect::string()->required(),
            "pcgw" => Expect::string()->required(),
            "steampeek" => Expect::string()->required(),
            "itad" => Expect::string()->required()
        ]);
    }

    public function getWSGFEndpoint(int $appid): string {
        return sprintf($this->config->wsgf, $appid);
    }

    public function getKeyLolEndpoint(): string {
        return $this->config->keylol;
    }

    public function getSteamSpyEndpoint(int $appid): string {
        return sprintf($this->config->steamspy, $appid);
    }

    public function getSteamChartsEndpoint(int $appid): string {
        return sprintf($this->config->steamcharts, $appid);
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

    public function getSteamPeekEndpoint(int $appid, string $key): string {
        return sprintf($this->config->steampeek, $appid, $key);
    }

    public function getIsThereAnyDealApiHost(): string {
        return $this->config->itad;
    }
}
