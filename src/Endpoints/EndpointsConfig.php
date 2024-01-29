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

    public function getWSGFEndpoint(int $appid): string {
        return sprintf($this->config->wsgf, $appid);
    }

    public function getSteamSpyEndpoint(int $appid): string {
        return sprintf($this->config->steamspy, $appid);
    }

    public function getSteamChartsEndpoint(int $appid): string {
        return sprintf($this->config->steamcharts, $appid);
    }

    public function getSteamRepEndpoint(int $steamId): string {
        return sprintf($this->config->steamrep, $steamId);
    }

    public function getSteamPeekEndpoint(int $appid, string $key): string {
        return sprintf($this->config->steampeek, $appid, $key);
    }

    public function getIsThereAnyDealApiHost(): string {
        return $this->config->itad;
    }
}
