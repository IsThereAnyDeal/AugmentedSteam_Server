<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

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

    public function getRep(): ?string {
        return $this->rep;
    }

    public function setRep(?string $rep): self {
        $this->rep = $rep;
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

    public function setChecked(bool $checked): self {
        $this->checked = $checked;
        return $this;
    }
}
