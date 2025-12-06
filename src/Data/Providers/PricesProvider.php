<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Providers;

use AugmentedSteam\Server\Data\Interfaces\PricesProviderInterface;
use AugmentedSteam\Server\Data\Objects\Prices;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use AugmentedSteam\Server\Lib\Redis\ERedisKey;
use AugmentedSteam\Server\Lib\Redis\RedisClient;
use Ds\Set;
use GuzzleHttp\Client;

class PricesProvider implements PricesProviderInterface
{
    const int CachableIdsLimit = 10;
    const int OverviewTTL = 5*60;
    const int GidsTTL = 6*60*60;

    public function __construct(
        private readonly Client $guzzle,
        private readonly RedisClient $redis,
        private readonly EndpointBuilder $endpoints
    ) {}

    /**
     * @param  list<string> $steamIds
     * @return array<string, string>  SteamId:GID
     */
    private function fetchIdMap(array $steamIds): array {
        $key = ERedisKey::Gids->value;

        /** @var array<string, string> $result */
        $result = [];

        $toFetch = new Set($steamIds);

        $cached = $this->redis->hmget($key, $steamIds);
        foreach($cached as $i => $gid) {
            $id = $steamIds[$i];
            if (!empty($gid)) {
                $result[$id] = $gid;
                $toFetch->remove($id);
            }
        }

        if (count($toFetch) > 0) {
            $endpoint = $this->endpoints->getSteamIdLookup();
            $response = $this->guzzle->post($endpoint, [
                "body" => json_encode($toFetch->toArray()),
                "headers" => [
                    "content-type" => "application/json",
                    "accept" => "application/json"
                ]
            ]);
            if ($response->getStatusCode() != 200) {
                return [];
            }

            $json = json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
            if (!is_array($json)) {
                return [];
            }

            if (!empty($json)) {
                $this->redis->hmset($key, $json);
                $this->redis->hexpire($key, self::GidsTTL, array_keys($json), "NX");
            }

            foreach($json as $id => $gid) {
                $result[$id] = $gid;
            }
        }

        return $result;
    }

    /**
     * @param list<int> $shops
     * @param list<string> $gids
     * @return array<mixed>
     */
    private function fetchOverview(string $country, array $shops, array $gids, bool $withVouchers): array {
        $endpoint = $this->endpoints->getPrices($country, $shops, $withVouchers);

        $response = $this->guzzle->post($endpoint, [
            "body" => json_encode($gids),
            "headers" => [
                "content-type" => "application/json",
                "accept" => "application/json"
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        $json = json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($json)) {
            return [];
        }
        return $json;
    }

    /**
     * @param list<string> $steamIds
     * @param list<int> $shops
     * @return ?Prices
     */
    public function fetch(
        array $steamIds,
        array $shops,
        string $country,
        bool $withVouchers
    ): ?Prices {
        $region = match($country) {
            // covered
            "FR", "US", "GB", "CA", "BR", "AU", "TR", "CN", "IN", "KR", "JP", "ID", "TW" => $country,
            // eu
            "AL", "AD", "AT", "BE", "DK", "FI", "IE", "LI", "LU", "MK", "NL", "SE", "CH", "DE",
            "BA", "BG", "HR", "CY", "CZ", "GR", "HU", "IT", "MT", "MC", "ME", "NO", "PL", "PT", "RO", "SM", "RS", "SK",
            "SI", "ES", "VA", "EE", "LV", "LT" => "FR",
            // fallback
            default => "US"
        };

        $key = ERedisKey::PriceOverview->value;
        $field = md5(json_encode([$steamIds, $shops, $region, $withVouchers], flags: JSON_THROW_ON_ERROR));

        if (count($steamIds) <= self::CachableIdsLimit) {
            $gzcached = $this->redis->hget($key, $field);
            if (!empty($gzcached)) {
                $cached = gzuncompress($gzcached);
                if ($cached) {
                    return Prices::fromJson($cached);
                }
            }
        }

        $map = array_filter($this->fetchIdMap($steamIds));
        if (empty($map)) {
            return null;
        }

        $gids = array_values($map);
        $overview = $this->fetchOverview($country, $shops, $gids, $withVouchers);
        if (empty($overview)) {
            return null;
        }

        /**
         * @var array{
         *     prices: list<array<mixed>>,
         *     bundles: list<array<mixed>>
         * } $overview
         */

        $gidMap = array_flip($map);

        $prices = new Prices();
        $prices->prices = [];
        $prices->bundles = $overview['bundles'];

        /**
         * @var array{
         *     id: string,
         *     current: array<string, mixed>,
         *     lowest: array<string, mixed>,
         *     bundled: number,
         *     urls: array{game: string}
         * } $game
         */
        foreach($overview['prices'] as $game) {
            $gid = $game['id'];
            $steamId = $gidMap[$gid];
            $prices->prices[$steamId] = [
                "current" => $game['current'],
                "lowest" => $game['lowest'],
                "bundled" => $game['bundled'],
                "urls" => [
                    "info" => $game['urls']['game']."info/",
                    "history" => $game['urls']['game']."history/",
                ]
            ];
        }

        $gz = gzcompress(json_encode($prices, flags: JSON_THROW_ON_ERROR));
        if ($gz) {
            $this->redis->hset($key, $field, $gz);
            $this->redis->hexpire($key, self::OverviewTTL, [$field], "NX");
        }
        return $prices;
    }
}
