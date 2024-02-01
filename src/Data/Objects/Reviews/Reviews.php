<?php
namespace AugmentedSteam\Server\Data\Objects\Reviews;

class Reviews implements \JsonSerializable
{
    public ?Review $metauser = null;
    public ?Review $opencritic = null;

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function jsonSerialize(): array {
        return [
            "metauser" => $this->metauser,
            "opencritic" => $this->opencritic
        ];
    }
}
