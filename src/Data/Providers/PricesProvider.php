<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Providers;

use AugmentedSteam\Server\Data\Interfaces\PricesProviderInterface;
use AugmentedSteam\Server\Data\Objects\Prices;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use GuzzleHttp\Client;

class PricesProvider implements PricesProviderInterface
{
    public function __construct(
        private readonly Client $guzzle,
        private readonly EndpointBuilder $endpoints
    ) {}

    /**
     * @param  list<string> $steamIds
     * @return array<string, string>  SteamId:GID
     */
    private function fetchIdMap(array $steamIds): array {
        $endpoint = $this->endpoints->getSteamIdLookup();

        $response = $this->guzzle->post($endpoint, [
            "body" => json_encode($steamIds),
            "headers" => [
                "content-type" => "application/json",
                "accept" => "application/json"
            ]
        ]);
        if ($response->getStatusCode() != 200) {
            return [];
        }

        return json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @param list<int> $shops
     * @param list<string> $gids
     * @return array<mixed>
     */
    private function fetchOverview(string $country, array $shops, array $gids): array {
        $endpoint = $this->endpoints->getPrices($country, $shops);

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

        return json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @var list<string> $steamIds
     * @var list<int> $shops
     * @return ?Prices
     */
    public function fetch(
        array $steamIds,
        array $shops,
        string $country
    ): ?Prices {

        $map = $this->fetchIdMap($steamIds);
        if (empty($map) || !is_array($map)) {
            return null;
        }

        $gids = array_values($map);
        $overview = $this->fetchOverview($country, $shops, $gids);
        if (empty($overview) || !is_array($overview)) {
            return null;
        }

        $gidMap = array_flip($map);

        $prices = new Prices();
        $prices->prices = [];
        $prices->bundles = $overview['bundles'];

        foreach($overview['prices'] as $game) {
            $gid = $game['id'];
            $steamId = $gidMap[$gid];
            $prices->prices[$steamId] = [
                "current" => $game['current'],
                "lowest" => $game['lowest'],
                "urls" => [
                    "info" => "https://isthereanydeal.com/game/id:{$game['id']}/info/",
                    "history" => "https://isthereanydeal.com/game/id:{$game['id']}/history/",
                ]
            ];
        }
        return $prices;
    }
}
