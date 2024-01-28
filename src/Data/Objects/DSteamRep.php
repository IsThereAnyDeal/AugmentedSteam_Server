<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Objects;

class DSteamRep
{
    private int $steam64;
    private ?string $rep;
    private int $timestamp;
    private bool $checked;

    public function getSteam64(): int {
        return $this->steam64;
    }

    public function setSteam64(int $steam64): self {
        $this->steam64 = $steam64;
        return $this;
    }

    /**
     * @return list<string>
     */
    public function getReputation(): array {
        return empty($this->rep) ? [] : explode(",", $this->rep);
    }

    /**
     * @param ?list<string> $reputation
     */
    public function setReputation(?array $reputation): self {
        $this->rep = empty($reputation) ? null : implode(",", $reputation);
        $this->checked = !is_null($reputation);
        return $this;
    }

    public function getTimestamp(): int {
        return $this->timestamp;
    }

    public function setTimestamp(int $timestamp): self {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function isChecked(): bool {
        return $this->checked;
    }
}
