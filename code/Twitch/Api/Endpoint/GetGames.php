<?php
namespace Twitch\Api\Endpoint;

use GuzzleHttp\Client;

class GetGames extends AbstractPaginableEndpoint {

    private const METHOD = "GET";
    private const ENDPOINT = "games";

    public function __construct(Client $client) {
        parent::__construct($client, self::ENDPOINT, self::METHOD);
    }

    public function setGameId(int $twitchGameId): self {
        $this->setParam("id", $twitchGameId);
        return $this;
    }
}
