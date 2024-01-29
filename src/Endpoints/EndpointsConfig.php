<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Endpoints;

use AugmentedSteam\Server\Config\AConfig;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class EndpointsConfig extends AConfig
{
    protected function getSchema(): Schema {
        return Expect::structure([
            "wsgf" => Expect::string()->required(),
            "steamspy" => Expect::string()->required(),
            "steamcharts" => Expect::string()->required(),
            "steamrep" => Expect::string()->required(),
            "steampeek" => Expect::string()->required(),
            "itad" => Expect::string()->required()
        ]);
    }

    public function getWSGFEndpoint(): string {
        return $this->config->wsgf;
    }

    public function getSteamSpyEndpoint(int $appid): string {
        return sprintf($this->config->steamspy, $appid);
    }

    public function getSteamChartsEndpoint(int $appid): string {
        return sprintf($this->config->steamcharts, $appid);
    }

    public function getSteamRepEndpoint(): string {
        return $this->config->steamrep;
    }

    public function getSteamPeekEndpoint(): string {
        return $this->config->steampeek;
    }

    public function getIsThereAnyDealApiHost(): string {
        return $this->config->itad;
    }
}
