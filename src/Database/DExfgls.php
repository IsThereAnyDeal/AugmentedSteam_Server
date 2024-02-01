<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

class DExfgls
{
    private int $appid;
    private bool $excluded;
    private bool $checked;
    private int $timestamp;

    public function getAppid(): int {
        return $this->appid;
    }

    public function setAppid(int $appid): self {
        $this->appid = $appid;
        return $this;
    }

    public function isExcluded(): bool {
        return $this->excluded;
    }

    public function setExcluded(bool $excluded): self {
        $this->excluded = $excluded;
        return $this;
    }

    public function isChecked(): bool {
        return $this->checked;
    }

    public function setChecked(bool $checked): self {
        $this->checked = $checked;
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
