<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DSteamRep extends AInsertableObject implements ISelectable
{
    protected int $steam64;
    protected ?string $rep;
    protected int $timestamp;
    protected int $checked;

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
        return $this->checked == 1;
    }

    public function setChecked(bool $checked): self {
        $this->checked = $checked ? 1 : 0;
        return $this;
    }
}
