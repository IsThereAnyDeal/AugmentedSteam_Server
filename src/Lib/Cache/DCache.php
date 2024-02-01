<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Lib\Cache;

class DCache
{
    private ECacheKey $key;
    private string $field;
    private ?string $data;
    private int $expiry;

    public function getKey(): ECacheKey {
        return $this->key;
    }

    public function setKey(ECacheKey $key): self {
        $this->key = $key;
        return $this;
    }

    public function getField(): string {
        return $this->field;
    }

    public function setField(string $field): self {
        $this->field = $field;
        return $this;
    }

    public function getData(): ?string {
        return $this->data;
    }

    public function setData(?string $data): self {
        $this->data = $data;
        return $this;
    }

    public function getExpiry(): int {
        return $this->expiry;
    }

    public function setExpiry(int $expiry): self {
        $this->expiry = $expiry;
        return $this;
    }
}
