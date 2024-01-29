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

    public function getSteamIdLookup(): string {
        $host = $this->endpoints->getIsThereAnyDealApiHost();
        $key = $this->keys->getIsThereAnyDealApiKey();
        return $host."/internal/steam-lookup/v1?key={$key}";
    }

    /**
     * @param list<int> $shops
     */
    public function getPrices(string $country, array $shops): string {
        $host = $this->endpoints->getIsThereAnyDealApiHost();
        $key = $this->keys->getIsThereAnyDealApiKey();
        return $host."/games/overview/v2?".http_build_query([
            "key" => $key,
            "country" => $country,
            "shops" => implode(",", $shops)
        ]);
    }

    public function getTwitchStream(string $channel): string {
        $host = $this->endpoints->getIsThereAnyDealApiHost();
        $key = $this->keys->getIsThereAnyDealApiKey();
        return $host."/internal/twitch/{$channel}/stream/v1?key={$key}";
    }

    public function getPlayers(int $appid): string {
        $host = $this->endpoints->getIsThereAnyDealApiHost();
        $key = $this->keys->getIsThereAnyDealApiKey();
        return $host."/internal/players/{$appid}/v1?key={$key}";
    }
}
