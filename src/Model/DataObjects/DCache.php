<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DCache extends AInsertableObject implements ISelectable
{
    protected int $appid;
    protected int $key;
    protected string $json;
    protected int $timestamp;

    public function getAppid(): int {
        return $this->appid;
    }

    public function setAppid(int $appid): self {
        $this->appid = $appid;
        return $this;
    }

    public function getKey(): int {
        return $this->key;
    }

    public function setKey(int $key): self {
        $this->key = $key;
        return $this;
    }

    public function getJson(): string {
        return $this->json;
    }

    public function setJson(string $json): self {
        $this->json = $json;
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
