<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DSteamSpy extends AInsertableObject implements ISelectable
{
    protected int $appid;
    protected string $owners;
    protected int $average_forever;
    protected int $average_2weeks;
    protected int $timestamp;

    public function getAppid(): int {
        return $this->appid;
    }

    public function setAppid(int $appid): self {
        $this->appid = $appid;
        return $this;
    }

    public function getOwners(): string {
        return $this->owners;
    }

    public function setOwners(string $owners): self {
        $this->owners = $owners;
        return $this;
    }

    public function getAverageForever(): int {
        return $this->average_forever;
    }

    public function setAverageForever(int $average_forever): self {
        $this->average_forever = $average_forever;
        return $this;
    }

    public function getAverage2weeks(): int {
        return $this->average_2weeks;
    }

    public function setAverage2weeks(int $average_2weeks): self {
        $this->average_2weeks = $average_2weeks;
        return $this;
    }

    public function getTimestamp(): int {
        return $this->timestamp;
    }

    public function setTimestamp(int $timestamp): self {
        $this->timestamp = $timestamp;
        return $this;
    }


}
