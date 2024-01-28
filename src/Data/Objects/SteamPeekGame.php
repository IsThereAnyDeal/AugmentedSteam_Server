<?php
namespace AugmentedSteam\Server\Data\Objects;

class SteamPeekGame {
    public string $title;
    public int $appid;
    public float $rating;
    public float $score;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array {
        return [
            "title" => $this->title,
            "appid" => $this->appid,
            "rating" => $this->rating,
            "score" => $this->score
        ];
    }

    /**
     * @param array<string, mixed> $array
     */
    public function fromArray(array $array): self {
        $this->title = $array['title'];
        $this->appid = $array['appid'];
        $this->rating = $array['rating'];
        $this->score = $array['score'];
        return $this;
    }
}
