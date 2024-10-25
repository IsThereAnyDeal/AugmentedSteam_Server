<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Providers;

use AugmentedSteam\Server\Data\Interfaces\ExfglsProviderInterface;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use AugmentedSteam\Server\Lib\Loader\SimpleLoader;

class ExfglsProvider implements ExfglsProviderInterface
{
    public function __construct(
        private readonly SimpleLoader $loader,
        private readonly EndpointBuilder $endpoints
    ) {}

    public function fetch(int $appid): bool {
        $endpoint = $this->endpoints->getExfgls();
        $response = $this->loader->post($endpoint, json_encode([$appid]));

        if (is_null($response)) {
            throw new \Exception();
        }

        /**
         * @var array<string, bool> $data
         */
        $data = json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new \Exception();
        }

        return array_key_exists((string)$appid, $data) && $data[(string)$appid];
    }
}
