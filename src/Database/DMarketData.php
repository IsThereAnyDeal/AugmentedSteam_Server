<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

class DMarketData
{
    private string $hash_name;
    private int $appid;
    private string $appname;
    private string $name;
    private int $sell_listings;
    private int $sell_price_usd;
    private string $img;
    private string $url;
    private string $type;
    private string $rarity;
    private int $timestamp;

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

    public function getAppName(): string {
        return $this->appname;
    }

    public function setAppName(string $appname): self {
        $this->appname = $appname;
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

    public function getImg(): string {
        return $this->img;
    }

    public function setImg(string $img): self {
        $this->img = $img;
        return $this;
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function setUrl(string $url): self {
        $this->url = $url;
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
