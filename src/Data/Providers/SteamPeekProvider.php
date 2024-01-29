<?php
namespace AugmentedSteam\Server\Data\Providers;

use AugmentedSteam\Server\Data\Interfaces\SteamPeekProviderInterface;
use AugmentedSteam\Server\Data\Objects\SteamPeekGame;
use AugmentedSteam\Server\Data\Objects\SteamPeekResults;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use AugmentedSteam\Server\Loader\SimpleLoader;
use Psr\Log\LoggerInterface;

class SteamPeekProvider implements SteamPeekProviderInterface {

    public function __construct(
        private readonly SimpleLoader $loader,
        private readonly EndpointBuilder $endpoints,
        private readonly LoggerInterface $logger
    ) {}

    public function fetch(int $appid): ?SteamPeekResults {
        $endpoint = $this->endpoints->getSteamPeek($appid);
        $response = $this->loader->get($endpoint);

        if (!is_null($response)) {
            $json = json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);

            if (!empty($json['response'])) {
                $response = $json['response'];

                if (!empty($response['success']) && $response['success'] === 1
                 && !empty($response['results'])
                ) {
                    $this->logger->info((string)$appid);

                    $results = new SteamPeekResults();
                    $results->games = array_map(function(array $a) {
                        $game = new SteamPeekGame();
                        $game->title = $a['title'];
                        $game->appid = intval($a['appid']);
                        $game->rating = floatval($a['sprating']);
                        $game->score = floatval($a['score']);
                        return $game;
                    }, $response['results']);
                    return $results;
                }
            }
        }

        $this->logger->error((string)$appid);
        return null;
    }
}
