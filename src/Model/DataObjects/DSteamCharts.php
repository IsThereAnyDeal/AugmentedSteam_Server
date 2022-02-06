<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DSteamCharts extends AInsertableObject implements ISelectable
{
    protected int $appid;
    protected ?int $recent;
    protected ?int $peak_day;
    protected ?int $peak_all;
    protected int $timestamp;

    public function getAppid(): int {
        return $this->appid;
    }

    public function setAppid(int $appid): self {
        $this->appid = $appid;
        return $this;
    }

    public function getRecent(): ?int {
        return $this->recent;
    }

    public function setRecent(?int $recent): self {
        $this->recent = $recent;
        return $this;
    }

    public function getPeakDay(): ?int {
        return $this->peak_day;
    }

    public function setPeakDay(?int $peak_day): self {
        $this->peak_day = $peak_day;
        return $this;
    }

    public function getPeakAll(): ?int {
        return $this->peak_all;
    }

    public function setPeakAll(?int $peak_all): self {
        $this->peak_all = $peak_all;
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
