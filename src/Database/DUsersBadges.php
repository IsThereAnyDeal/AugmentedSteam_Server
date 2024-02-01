<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DUsersBadges extends AInsertableObject implements ISelectable
{
    protected int $steam64;
    protected int $badge_id;

    public function getSteam64(): int {
        return $this->steam64;
    }

    public function setSteam64(int $steam64): self {
        $this->steam64 = $steam64;
        return $this;
    }

    public function getBadgeId(): int {
        return $this->badge_id;
    }

    public function setBadgeId(int $badge_id): self {
        $this->badge_id = $badge_id;
        return $this;
    }
}
