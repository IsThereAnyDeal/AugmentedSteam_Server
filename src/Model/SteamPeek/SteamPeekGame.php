<?php
namespace AugmentedSteam\Server\Model\SteamPeek;

class SteamPeekGame {

    private array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function getTitle(): string {
        return $this->data['title'];
    }

    public function getAppid(): int {
        return (int)$this->data['appid'];
    }

    public function getSPRating(): float {
        return (float)$this->data['sprating'];
    }

    public function getScore(): float {
        return (float)$this->data['score'];
    }
}
