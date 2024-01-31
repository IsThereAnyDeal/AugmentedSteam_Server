<?php
namespace AugmentedSteam\Server\Data\Objects;

use JsonSerializable;
use Override;

class TwitchStream implements JsonSerializable
{
    public string $userName;
    public string $title;
    public string $thumbnailUrl;
    public int $viewerCount;
    public string $game;

    #[Override]
    public function jsonSerialize() {
        return [
            "user_name" => $this->userName,
            "game" => $this->game,
            "view_count" => $this->viewerCount,
            "thumbnail_url" => $this->thumbnailUrl
        ];
    }
}
