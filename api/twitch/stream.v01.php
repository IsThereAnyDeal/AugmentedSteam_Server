<?php
require_once __DIR__ . "/../../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint())
    ->params(["channel"]);

$guzzle = new \GuzzleHttp\Client();
try {
    $token = (new \Twitch\Api\Authorization($guzzle))->getToken();

    $streams = new \Twitch\Api\Endpoint\GetStreams($guzzle);
    $streams->setToken($token);
    $streams->setUserLogin($endpoint->getParam("channel"));
    $stream = $streams->getItemEnumerator()->current();

    if (is_null($stream)) {
        (new \Api\Response())
            ->data([])
            ->respond();
    }

    $games = new \Twitch\Api\Endpoint\GetGames($guzzle);
    $games->setToken($token);
    $games->setGameId($stream['game_id']);
    $game = $games->getItemEnumerator()->current();

    $response = [
        "user_name" => $stream['user_name'],
        "title" => $stream['title'],
        "thumbnail_url" => $stream['thumbnail_url'],
        "viewer_count" => $stream['viewer_count'],
        "game" => $game['name'],
    ];

} catch (\Exception $e) {
    (new \Api\Response())->fail();
}

(new \Api\Response())
    ->data($response)
    ->respond();
