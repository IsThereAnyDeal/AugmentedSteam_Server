<?php
namespace AugmentedSteam\Server\Data\Objects;

class HLTB implements \JsonSerializable
{
    public ?int $story;
    public ?int $extras;
    public ?int $complete;
    public string $url;

    #[\Override]
    public function jsonSerialize(): array {
        return [
            "story" => $this->story,
            "extras" => $this->extras,
            "complete" => $this->complete,
            "url" => $this->url
        ];
    }
}
