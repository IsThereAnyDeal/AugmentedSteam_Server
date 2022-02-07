<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DSimilar extends AInsertableObject implements ISelectable
{
    protected int $appid;
    protected ?string $data;
    protected int $timestamp;

    public function getAppid(): int {
        return $this->appid;
    }

    public function setAppid(int $appid): self {
        $this->appid = $appid;
        return $this;
    }

    public function getData(): ?array {
        return is_null($this->data)
            ? null
            : json_decode($this->data, true);
    }

    public function setData(?array $data): self {
        $this->data = empty($data) ? null : json_encode($data);
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
