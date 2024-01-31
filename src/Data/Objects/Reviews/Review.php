<?php
namespace AugmentedSteam\Server\Data\Objects\Reviews;

class Review
{
    public function __construct(
        public ?int $score,
        public ?string $verdict,
        public string $url
    ) {}
}
