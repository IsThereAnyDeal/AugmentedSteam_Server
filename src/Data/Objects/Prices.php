<?php
namespace AugmentedSteam\Server\Data\Objects;

use JsonSerializable;

class Prices implements JsonSerializable
{
    public static function fromJson(string $json): self {
        /** @var array<string, mixed> $data */
        $data = json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
        $prices = new Prices();
        $prices->prices = $data['prices']; // @phpstan-ignore-line
        $prices->bundles = $data['bundles']; // @phpstan-ignore-line
        return $prices;
    }

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
