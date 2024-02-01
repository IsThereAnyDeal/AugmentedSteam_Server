<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DGameDlc extends AInsertableObject implements ISelectable
{
    protected int $id;
    protected int $appid;
    protected int $dlc_category;

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): self {
        $this->id = $id;
        return $this;
    }

    public function getAppid(): int {
        return $this->appid;
    }

    public function setAppid(int $appid): self {
        $this->appid = $appid;
        return $this;
    }

    public function getDlcCategory(): int {
        return $this->dlc_category;
    }

    public function setDlcCategory(int $dlc_category): self {
        $this->dlc_category = $dlc_category;
        return $this;
    }
}
