<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\IsThereAnyDeal;

class ReviewsData
{
    private ?MetaCriticData $metaCritic = null;
    private ?OpenCriticData $openCritic = null;

    public function __construct(array $data) {
        if (isset($data['metacritic'])) {
            $this->metaCritic = new MetaCriticData($data['metacritic']);
        }

        if (isset($data['opencritic'])) {
            $this->openCritic = new OpenCriticData($data['opencritic']);
        }
    }

    public function getMetaCritic(): ?MetaCriticData {
        return $this->metaCritic;
    }

    public function getOpenCritic(): ?OpenCriticData {
        return $this->openCritic;
    }
}
