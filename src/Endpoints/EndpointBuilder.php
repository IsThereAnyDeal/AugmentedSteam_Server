<?php
namespace AugmentedSteam\Server\Endpoints;

class EndpointBuilder
{
    public function __construct(
        private readonly EndpointsConfig $endpoints,
        private readonly KeysConfig $keys
    ) {}

    public function getWSGF(int $appid): string {
        return sprintf($this->endpoints->getWSGFEndpoint(), $appid);
    }

    public function getSteamRep(int $steamId): string {
        return sprintf($this->endpoints->getSteamRepEndpoint(), $steamId);
    }

    public function getSteamPeek(int $appid): string {
        return sprintf($this->endpoints->getSteamPeekEndpoint(), $appid, $this->keys->getSteamPeekApiKey());
    }

    public function getEarlyAccess(): string {
        $host = $this->endpoints->getIsThereAnyDealApiHost();
        $key = $this->keys->getIsThereAnyDealApiKey();
        return $host."/internal/early-access/v1?key={$key}";
    }
}
