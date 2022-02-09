<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DSession extends AInsertableObject implements ISelectable
{
    protected string $token;
    protected string $hash;
    protected int $steam_id;
    protected int $expiry;

    public function getToken(): string {
        return $this->token;
    }

    public function setToken(string $token): self {
        $this->token = $token;
        return $this;
    }

    public function getHash(): string {
        return $this->hash;
    }

    public function setHash(string $hash): self {
        $this->hash = $hash;
        return $this;
    }

    public function getSteamId(): int {
        return $this->steam_id;
    }

    public function setSteamId(int $steam_id): self {
        $this->steam_id = $steam_id;
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
