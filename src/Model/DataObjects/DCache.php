<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use AugmentedSteam\Server\Model\Cache\ECacheKey;

class DCache
{
    private int $appid;
    private ECacheKey $key;
    private string $json;
    private int $timestamp;

    public function getAppid(): int {
        return $this->appid;
    }

    public function setAppid(int $appid): self {
        $this->appid = $appid;
        return $this;
    }

    public function getKey(): ECacheKey {
        return $this->key;
    }

    public function setKey(ECacheKey $key): self {
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
