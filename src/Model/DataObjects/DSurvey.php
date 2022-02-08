<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DSurvey extends AInsertableObject implements ISelectable
{
    protected int $appid;
    protected int $steamid;
    protected ?int $framerate;
    protected ?int $optimized;
    protected ?int $lag;
    protected ?string $graphics_settings;
    protected ?int $bg_sound_mute;
    protected ?int $good_controls;
    protected ?int $timestamp;

    public function getAppid(): int {
        return $this->appid;
    }

    public function setAppid(int $appid): self {
        $this->appid = $appid;
        return $this;
    }

    public function getSteamid(): int {
        return $this->steamid;
    }

    public function setSteamId(int $steamid): self {
        $this->steamid = $steamid;
        return $this;
    }

    public function getFramerate(): ?int {
        return $this->framerate;
    }

    public function setFramerate(?int $framerate): self {
        $this->framerate = $framerate;
        return $this;
    }

    public function getOptimized(): ?int {
        return $this->optimized;
    }

    public function setOptimized(?int $optimized): self {
        $this->optimized = $optimized;
        return $this;
    }

    public function getLag(): ?int {
        return $this->lag;
    }

    public function setLag(?int $lag): self {
        $this->lag = $lag;
        return $this;
    }

    public function getGraphicsSettings(): ?string {
        return $this->graphics_settings;
    }

    public function setGraphicsSettings(?string $graphics_settings): self {
        $this->graphics_settings = $graphics_settings;
        return $this;
    }

    public function getBgSoundMute(): ?int {
        return $this->bg_sound_mute;
    }

    public function setBgSoundMute(?int $bg_sound_mute): self {
        $this->bg_sound_mute = $bg_sound_mute;
        return $this;
    }

    public function getGoodControls(): ?int {
        return $this->good_controls;
    }

    public function setGoodControls(?int $good_controls): self {
        $this->good_controls = $good_controls;
        return $this;
    }

    public function getTimestamp(): ?int {
        return $this->timestamp;
    }

    public function setTimestamp(?int $timestamp): self {
        $this->timestamp = $timestamp;
        return $this;
    }
}
