<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Loader;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class SimpleLoader
{
    private Client $guzzle;
    private LoggerInterface $logger;

    public function __construct(Client $guzzle, LoggerInterface $logger) {
        $this->guzzle = $guzzle;
        $this->logger = $logger;
    }

    public function get(string $url, array $curlOptions = []): ?ResponseInterface {
        try {
            return $this->guzzle->get($url, [
                "headers" => [
                    "User-Agent" => "AugmentedSteam/1.0 (+bots@isthereanydeal.com)",
                ],
                "curl" => $curlOptions
            ]);
        } catch (GuzzleException $e) {
            $this->logger->error($e);
        }
        return null;
    }
}
