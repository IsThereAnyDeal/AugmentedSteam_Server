<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

class DUsersProfiles
{
    private int $steam64;
    private ?string $bg_img;
    private ?int $bg_appid;
    private ?string $style;

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
}
