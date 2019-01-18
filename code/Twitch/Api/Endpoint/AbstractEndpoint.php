<?php
namespace Twitch\Api\Endpoint;

use GuzzleHttp\Client;
use Twitch\Api\Exception\InvalidResponseException;
use Twitch\Api\Exception\RateLimitedException;
use Twitch\Api\Exception\UnsupportedResponseTypeException;
use Twitch\Api\Response\AbstractResponse;
use Twitch\Api\Response\DataResponse;
use Twitch\Api\Response\ErrorResponse;
use Twitch\Api\Token;

abstract class AbstractEndpoint {

    private const HOST = "https://api.twitch.tv/helix/";

    private $client;

    /** @var Token */
    private $token = null;

    private $rateLimit = null;
    private $rateLimitRemaining = null;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    public function setToken(?Token $token) {
        if (!is_null($token)) {
            $this->token = $token;
        }
        return $this;
    }

    private function getHeaders(): array {
        $headers = [
            "Client-Id" => \Config::TwitchApiKey
        ];

        if (!is_null($this->token)) {
            $headers['Authorization'] = "Bearer {$this->token}";
        }

        return $headers;
    }

    /**
     * @param string $endpoint
     * @param array $params
     * @param string $method
     * @return AbstractResponse
     * @throws InvalidResponseException
     * @throws UnsupportedResponseTypeException
     * @throws RateLimitedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    final protected function execute(string $endpoint, array $params, string $method="GET"): AbstractResponse {

        $response = $this->client->request($method,self::HOST.$endpoint, [
            "query" => $params,
            "headers" => $this->getHeaders()
        ]);

        if ($response->getHeader("Ratelimit-Limit")) {
            $this->rateLimit = $response->getHeader("Ratelimit-Limit")[0];
        }

        if ($response->getHeader("Ratelimit-Remaining")) {
            $this->rateLimitRemaining = $response->getHeader("Ratelimit-Remaining")[0];
        }

        if ($response->getStatusCode() == 429) {
            throw new RateLimitedException();
        }

        $json = json_decode($response->getBody(), true);
        if ($json === false) {
            throw new InvalidResponseException();
        }

        if (isset($json['error'])) {
            return new ErrorResponse($response->getHeaders(), $json);
        }

        if (isset($json['data'])) {
            return new DataResponse($response->getHeaders(), $json);
        }

        throw new UnsupportedResponseTypeException();
    }

    public function getRateLimit(): ?int {
        return $this->rateLimit;
    }

    public function getRateLimitRemaining(): ?int {
        return $this->rateLimitRemaining;
    }
}
