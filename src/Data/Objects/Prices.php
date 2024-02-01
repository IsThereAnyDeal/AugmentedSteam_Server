<?php
namespace AugmentedSteam\Server\Data\Objects;

use JsonSerializable;

class Prices implements JsonSerializable
{
    /**
     * @var array<string, array{
     *     current: array<mixed>,
     *     lowest: array<mixed>,
     *     urls: array{
     *         info: string,
     *         history: string
     *     }
     * }>
     */
    public array $prices;

    /**
     * @var list<array<mixed>>
     */
    public array $bundles;

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array {
        return [
            "prices" => $this->prices,
            "bundles" => $this->bundles
        ];
    }
}
