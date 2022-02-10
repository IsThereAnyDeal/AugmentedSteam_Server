<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DHLTB extends AInsertableObject implements ISelectable
{
    protected int $id;
    protected ?int $appid;
    protected ?int $main;
    protected ?int $extra;
    protected ?int $complete;
    protected int $found_timestamp;
    protected ?int $checked_timestamp;

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): self {
        $this->id = $id;
        return $this;
    }

    public function getAppid(): ?int {
        return $this->appid;
    }

    public function setAppid(?int $appid): self {
        $this->appid = $appid;
        return $this;
    }

    private function getTimeString(?int $time): string {
        if (is_null($time)) {
            return "";
        }

        $hours = floor($time/60);
        $minutes = $time % 60;
        if ($minutes == 0) {
            return "{$hours}h";
        } else {
            return "{$hours}h ".($minutes < 10 ? "0{$minutes}" : $minutes)."m";
        }
    }

    public function getMain(): ?int {
        return $this->main;
    }

    public function getMainString(): string {
        return $this->getTimeString($this->main);
    }

    public function setMain(?float $main): self {
        $this->main = $main;
        return $this;
    }

    public function getExtra(): ?int {
        return $this->extra;
    }

    public function getExtraString(): string {
        return $this->getTimeString($this->extra);
    }

    public function setExtra(?float $extra): self {
        $this->extra = $extra;
        return $this;
    }

    public function getComplete(): ?int {
        return $this->complete;
    }

    public function getCompleteString(): string {
        return $this->getTimeString($this->complete);
    }

    public function setComplete(?float $complete): self {
        $this->complete = $complete;
        return $this;
    }

    public function getFoundTimestamp(): int {
        return $this->found_timestamp;
    }

    public function setFoundTimestamp(int $found_timestamp): self {
        $this->found_timestamp = $found_timestamp;
        return $this;
    }

    public function getCheckedTimestamp(): ?int {
        return $this->checked_timestamp;
    }

    public function setCheckedTimestamp(?int $checked_timestamp): self {
        $this->checked_timestamp = $checked_timestamp;
        return $this;
    }
}
