<?php
namespace AugmentedSteam\Server\Data\Objects\SteamPeak;

class SteamPeekResults implements \JsonSerializable {

    /** @var SteamPeekGame */
    public array $games;

    public function shuffle(bool $shuffle): self {
        if ($shuffle) {
            shuffle($this->games);
        }
        return $this;
    }

    public function limit(int $count): self {
        $this->games = array_slice($this->games, 0, $count);
        return $this;
    }

    #[\Override]
    public function jsonSerialize(): array {
        return $this->games;
    }
}
