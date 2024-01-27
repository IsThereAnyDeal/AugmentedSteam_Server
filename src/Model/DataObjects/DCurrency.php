<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

class DCurrency
{
    private string $from;
    private string $to;
    private float $rate;
    private int $timestamp;

    public function getFrom(): string {
        return $this->from;
    }

    public function setFrom(string $from): self {
        $this->from = $from;
        return $this;
    }

    public function getTo(): string {
        return $this->to;
    }

    public function setTo(string $to): self {
        $this->to = $to;
        return $this;
    }

    public function getRate(): float {
        return $this->rate;
    }

    public function setRate(float $rate): self {
        $this->rate = $rate;
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
