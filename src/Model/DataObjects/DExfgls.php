<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DExfgls extends AInsertableObject implements ISelectable
{
    protected int $appid;
    protected int $excluded;
    protected int $checked;
    protected int $timestamp;

    public function getAppid(): int {
        return $this->appid;
    }

    public function setAppid(int $appid): self {
        $this->appid = $appid;
        return $this;
    }

    public function isExcluded(): bool {
        return $this->excluded === 1;
    }

    public function setExcluded(bool $excluded): self {
        $this->excluded = $excluded ? 1 : 0;
        return $this;
    }

    public function isChecked(): bool {
        return $this->checked == 1;
    }

    public function setChecked(bool $checked): self {
        $this->checked = $checked ? 1 : 0;
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
