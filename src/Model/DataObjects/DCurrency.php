<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DCurrency extends AInsertableObject implements ISelectable
{
    protected string $from;
    protected string $to;
    protected float $rate;
    protected int $timestamp;

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
