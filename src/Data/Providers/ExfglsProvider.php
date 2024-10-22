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

    /**
     * @param list<int> $appids
     * @return array<int, bool>
     */
    public function fetch(array $appids): array {
        $endpoint = $this->endpoints->getExfgls();
        $response = $this->loader->post($endpoint, json_encode($appids));

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

        $result = [];
        foreach($appids as $appid) {
            if (array_key_exists((string)$appid, $data)) {
                $result[$appid] = $data[(string)$appid];
            }
        }
        return $result;
    }
}
