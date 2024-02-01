<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Providers;

use AugmentedSteam\Server\Data\Interfaces\RatesProviderInterface;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use AugmentedSteam\Server\Lib\Loader\SimpleLoader;

class RatesProvider implements RatesProviderInterface
{
    public function __construct(
        private readonly SimpleLoader $loader,
        private readonly EndpointBuilder $endpoints
    ) {}

    /**
     * @return list<array{
     *     from: string,
     *     to: string,
     *     rate: float
     * }>
     */
    public function fetch(): array {
        $endpoint = $this->endpoints->getRates();

        $response = $this->loader->get($endpoint);
        if (!is_null($response)) {
            $data = json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }
}
