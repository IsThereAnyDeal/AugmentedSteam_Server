<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Providers;

use AugmentedSteam\Server\Data\Interfaces\GameIdsProviderInterface;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use GuzzleHttp\Client;

class GameIdsProvider implements GameIdsProviderInterface
{
    public function __construct(
        private readonly Client $guzzle,
        private readonly EndpointBuilder $endpoints
    ) {}

    /**
     * @param list<string> $ids
     * @return array<string, string>
     */
    public function fetch(array $ids): array {
        $endpoint = $this->endpoints->getSteamIdLookup();

        $response = $this->guzzle->post($endpoint, [
            "body" => json_encode($ids),
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
