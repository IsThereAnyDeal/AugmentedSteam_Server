<?php
namespace AugmentedSteam\Server\Data\Objects;

class SteamPeekResults {

    /** @var list<SteamPeekGame> */
    public array $games;

    /**
     * @return list<array<string, mixed>>
     */
    public function toArray(): array {
        return array_map(fn(SteamPeekGame $game) => $game->toArray(), $this->games);
    }

    /**
     * @param list<array<string, mixed>> $array
     */
    public function fromArray(array $array): self {
        $this->games = array_map(fn(array $a) => (new SteamPeekGame())->fromArray($a), $array);
        return $this;
    }
}
