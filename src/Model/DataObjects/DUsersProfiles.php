<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DUsersProfiles extends AInsertableObject implements ISelectable
{
    protected int $steam64;
    protected ?string $bg_img;
    protected ?int $bg_appid;
    protected ?string $style;
    protected string $update_time;

    public function getSteam64(): int {
        return $this->steam64;
    }

    public function setSteam64(int $steam64): self {
        $this->steam64 = $steam64;
        return $this;
    }

    public function getBgImg(): ?string {
        return $this->bg_img;
    }

    public function setBgImg(?string $bg_img): self {
        $this->bg_img = $bg_img;
        return $this;
    }

    public function getBgAppid(): ?int {
        return $this->bg_appid;
    }

    public function setBgAppid(?int $bg_appid): self {
        $this->bg_appid = $bg_appid;
        return $this;
    }

    public function getStyle(): ?string {
        return $this->style;
    }

    public function setStyle(?string $style): self {
        $this->style = $style;
        return $this;
    }

    public function getUpdateTime(): string {
        return $this->update_time;
    }

    public function setUpdateTime(string $update_time): self {
        $this->update_time = $update_time;
        return $this;
    }
}
