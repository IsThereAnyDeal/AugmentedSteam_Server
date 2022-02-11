<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Twitch;

use AugmentedSteam\Server\Config\TwitchConfig;
use GuzzleHttp\Client;
use IsThereAnyDeal\Twitch\Api\Authorization;
use IsThereAnyDeal\Twitch\Api\Credentials;
use IsThereAnyDeal\Twitch\Api\Endpoint\GetGames;
use IsThereAnyDeal\Twitch\Api\Endpoint\GetStreams;
use IsThereAnyDeal\Twitch\Api\TokenStorageInterface;

class TwitchManager
{
    private TwitchConfig $config;
    private TokenStorageInterface $tokenStorage;
    private Client $guzzle;

    public function __construct(
        TwitchConfig $config,
        TokenStorageInterface $tokenStorage,
        Client $guzzle
    ) {
        $this->config = $config;
        $this->tokenStorage = $tokenStorage;
        $this->guzzle = $guzzle;
    }

    public function getStream(string $channel): ?array {

        $credentials = new Credentials(
            $this->config->getClientId(),
            $this->config->getClientSecret()
        );

        $token = (new Authorization(
            $credentials,
            $this->tokenStorage,
            $this->guzzle
        ))->getToken();

        try {
            $streams = new GetStreams($credentials, $this->guzzle);
            $streams->setToken($token);
            $streams->setUserLogin($channel);
            $stream = $streams->getItemEnumerator()->current();
        } catch (\Exception $e) {
            $stream = null;
        }

        if (is_null($stream)) {
            return null;
        }

        $games = new GetGames($credentials, $this->guzzle);
        $games->setToken($token);
        $games->setGameId((int)$stream['game_id']);
        $game = $games->getItemEnumerator()->current();

        return [
            "user_name" => $stream['user_name'],
            "title" => $stream['title'],
            "thumbnail_url" => $stream['thumbnail_url'],
            "viewer_count" => $stream['viewer_count'],
            "game" => $game['name'],
        ];
    }
}
