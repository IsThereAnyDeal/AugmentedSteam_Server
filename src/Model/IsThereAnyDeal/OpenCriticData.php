<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\IsThereAnyDeal;

class OpenCriticData
{

    private string $url;
    private int $score;
    private string $award;
    private array $reviews;

    public function __construct(array $data) {
        $this->url = $data['url'];
        $this->score = $data['score'];
        $this->award = $data['award'];

        $this->reviews = [];
        foreach ($data['reviews'] as $review) {
            $this->reviews[] = new OpenCriticReview($review);
        }
    }

    public function getUrl() : string {
        return $this->url;
    }

    public function getScore(): int {
        return $this->score;
    }

    public function getAward(): string {
        return $this->award;
    }

    /** @return OpenCriticReview[] */
    public function getReviews(): array {
        return $this->reviews;
    }
}
