<?php
namespace AugmentedSteam\Server\Data\Objects\SteamPeak;

class SteamPeekGame implements \JsonSerializable {

    public string $title;
    public int $appid;
    public float $rating;
    public float $score;

    #[\Override]
    public function jsonSerialize(): array {
        return [
            "title" => $this->title,
            "appid" => $this->appid,
            "sprating" => $this->rating,
            "score" => $this->score
        ];
    }
}
