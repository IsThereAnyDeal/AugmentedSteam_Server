<?php
namespace AugmentedSteam\Server\Data\Providers;

use AugmentedSteam\Server\Data\Interfaces\EarlyAccessProviderInterface;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use AugmentedSteam\Server\Loader\SimpleLoader;

class EarlyAccessProvider implements EarlyAccessProviderInterface {

    public function __construct(
        private readonly SimpleLoader $loader,
        private readonly EndpointBuilder $endpoints
    ) {}

    /**
     * @return list<int>
     */
    public function fetch(): array {
        $response = $this->loader->get($this->endpoints->getEarlyAccess());

        $appids = [];
        if (!is_null($response)) {
            $appids = json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
            if (!is_array($appids) || !array_is_list($appids)) {
                $appids = [];
            }
        }

        return $appids;
    }
}
