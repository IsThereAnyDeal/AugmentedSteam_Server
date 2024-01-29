<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Providers;

use AugmentedSteam\Server\Data\Interfaces\PricesProviderInterface;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use GuzzleHttp\Client;

class PricesProvider implements PricesProviderInterface
{
    public function __construct(
        private readonly Client $guzzle,
        private readonly EndpointBuilder $endpoints
    ) {}

    /**
     * @var list<string> $gids
     * @var list<int> $shops
     */
    public function fetch(
        array $gids,
        array $shops,
        string $country
    ): array {
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
}
