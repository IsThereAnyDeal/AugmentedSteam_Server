<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DMarketData extends AInsertableObject implements ISelectable
{
    protected string $hash_name;
    protected int $appid;
    protected string $name;
    protected int $sell_listings;
    protected int $sell_price_usd;
    protected string $icon_url;
    protected string $type;
    protected string $rarity;
    protected int $timestamp;

    public function getHashName(): string {
        return $this->hash_name;
    }

    public function setHashName(string $hash_name): self {
        $this->hash_name = $hash_name;
        return $this;
    }

    public function getAppid(): int {
        return $this->appid;
    }

    public function setAppid(int $appid): self {
        $this->appid = $appid;
        return $this;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function getSellListings(): int {
        return $this->sell_listings;
    }

    public function setSellListings(int $sell_listings): self {
        $this->sell_listings = $sell_listings;
        return $this;
    }

    public function getSellPriceUsd(): int {
        return $this->sell_price_usd;
    }

    public function setSellPriceUsd(int $sell_price_usd): self {
        $this->sell_price_usd = $sell_price_usd;
        return $this;
    }

    public function getIconUrl(): string {
        return $this->icon_url;
    }

    public function setIconUrl(string $icon_url): self {
        $this->icon_url = $icon_url;
        return $this;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setType(string $type): self {
        $this->type = $type;
        return $this;
    }

    public function getRarity(): string {
        return $this->rarity;
    }

    public function setRarity(string $rarity): self {
        $this->rarity = $rarity;
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
