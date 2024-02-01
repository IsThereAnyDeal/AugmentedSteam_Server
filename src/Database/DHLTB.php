<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

class DHLTB
{
    private int $id;
    private ?int $appid;
    private ?int $main;
    private ?int $extra;
    private ?int $complete;
    private int $found_timestamp;
    private ?int $checked_timestamp;

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

    public function setMain(?int $main): self {
        $this->main = $main;
        return $this;
    }

    public function getExtra(): ?int {
        return $this->extra;
    }

    public function getExtraString(): string {
        return $this->getTimeString($this->extra);
    }

    public function setExtra(?int $extra): self {
        $this->extra = $extra;
        return $this;
    }

    public function getComplete(): ?int {
        return $this->complete;
    }

    public function getCompleteString(): string {
        return $this->getTimeString($this->complete);
    }

    public function setComplete(?int $complete): self {
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
