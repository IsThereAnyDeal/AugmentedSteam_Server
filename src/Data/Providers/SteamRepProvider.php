<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Providers;

use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Data\Interfaces\SteamRepProviderInterface;
use AugmentedSteam\Server\Loader\SimpleLoader;

class SteamRepProvider implements SteamRepProviderInterface {

    public function __construct(
        private readonly SimpleLoader $loader,
        private readonly EndpointsConfig $config
    ) {}

    /**
     * @return ?list<string>
     */
    public function getReputation(int $steamId): ?array {
        $url = $this->config->getSteamRepEndpoint($steamId);

        $response = $this->loader->get($url);
        if (is_null($response)) {
            return null;
        }

        $body = $response->getBody()->getContents();
        $json = json_decode($body, true, flags: JSON_THROW_ON_ERROR);

        if (isset($json['steamrep']['reputation']['full'])) {
            $reputation = $json['steamrep']['reputation']['full'];
            if (!empty($reputation)) {
                return explode(",", $reputation);
            }
        }
        return null;
    }
}