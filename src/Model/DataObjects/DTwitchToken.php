<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DTwitchToken extends AInsertableObject implements ISelectable
{
    protected string $token;
    protected int $expiry;

    public function getToken(): string {
        return $this->token;
    }

    public function setToken(string $token): self {
        $this->token = $token;
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
