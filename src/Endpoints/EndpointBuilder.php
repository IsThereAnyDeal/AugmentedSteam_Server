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
        return $host."/unstable/id-lookup/itad/61/v2?key={$key}";
    }

    /**
     * @param list<int> $shops
     */
    public function getPrices(string $country, array $shops, bool $withVouchers): string {
        $host = $this->endpoints->getIsThereAnyDealApiHost();
        $key = $this->keys->getIsThereAnyDealApiKey();
        return $host."/games/overview/v2?".http_build_query([
            "key" => $key,
            "country" => $country,
            "shops" => implode(",", $shops),
            "vouchers" => $withVouchers
        ]);
    }

    public function getTwitchStream(string $channel): string {
        $host = $this->endpoints->getIsThereAnyDealApiHost();
        $key = $this->keys->getIsThereAnyDealApiKey();
        return $host."/internal/twitch/stream/v1?key={$key}&channel={$channel}";
    }

    public function getPlayers(int $appid): string {
        $host = $this->endpoints->getIsThereAnyDealApiHost();
        $key = $this->keys->getIsThereAnyDealApiKey();
        return $host."/internal/players/v1?key={$key}&appid={$appid}";
    }

    public function getReviews(int $appid): string {
        $host = $this->endpoints->getIsThereAnyDealApiHost();
        $key = $this->keys->getIsThereAnyDealApiKey();
        return $host."/internal/reviews/v1?key={$key}&appid={$appid}";
    }

    public function getHLTB(int $appid): string {
        $host = $this->endpoints->getIsThereAnyDealApiHost();
        $key = $this->keys->getIsThereAnyDealApiKey();
        return $host."/internal/hltb/v1?key={$key}&appid={$appid}";
    }

    public function getRates(): string {
        $host = $this->endpoints->getIsThereAnyDealApiHost();
        $key = $this->keys->getIsThereAnyDealApiKey();
        return $host."/internal/rates/v1?key={$key}";
    }
}
