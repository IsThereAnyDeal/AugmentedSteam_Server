<?php
namespace AugmentedSteam\Server\Data\Objects\Reviews;

class Review implements \JsonSerializable
{
    public function __construct(
        public ?int $score,
        public ?string $verdict,
        public string $url
    ) {}

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function jsonSerialize(): array {
        return [
            "score" => $this->score,
            "verdict" => $this->verdict,
            "url" => $this->url
        ];
    }
}
