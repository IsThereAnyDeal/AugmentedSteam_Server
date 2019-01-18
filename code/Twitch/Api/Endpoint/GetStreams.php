<?php
namespace Twitch\Api\Endpoint;

use GuzzleHttp\Client;

class GetStreams extends AbstractPaginableEndpoint {

    private const METHOD = "GET";
    private const ENDPOINT = "streams";

    public function __construct(Client $client) {
        parent::__construct($client, self::ENDPOINT, self::METHOD);

        $this->setLanguage("en");
    }

    public function setGameId(int $twitchGameId): self {
        $this->setParam("game_id", $twitchGameId);
        return $this;
    }

    public function setLanguage(string $language): self {
        $this->setParam("language", $language);
        return $this;
    }

    public function setUserLogin(string $userLogin): self {
        $this->setParam("user_login", $userLogin);
        return $this;
    }
}
