<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\IsThereAnyDeal;

class MetaCriticData
{
    private int $userScore;

    public function __construct(array $data) {
        $this->userScore = $data['userscore'];
    }

    public function getUserScore(): int {
        return $this->userScore;
    }
}
