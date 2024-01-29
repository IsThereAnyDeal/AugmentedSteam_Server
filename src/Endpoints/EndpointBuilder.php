<?php
namespace AugmentedSteam\Server\Endpoints;

class EndpointBuilder
{
    public function __construct(
        private readonly EndpointsConfig $endpoints,
        private readonly KeysConfig $keys
    ) {}

    public function getEarlyAccess(): string {
        $host = $this->endpoints->getIsThereAnyDealApiHost();
        $key = $this->keys->getIsThereAnyDealApiKey();
        return $host."/internal/early-access/v1?key={$key}";
    }
}
