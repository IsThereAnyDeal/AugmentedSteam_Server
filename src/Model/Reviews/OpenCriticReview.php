<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Reviews;

class OpenCriticReview
{
    private array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function getPublishedDate(): string {
        return $this->data['publishedDate'];
    }

    public function getSnippet(): string {
        return $this->data['snippet'];
    }

    public function getDisplayScore(): string {
        return $this->data['displayScore'];
    }

    public function getExternalUrl(): string {
        return $this->data['externalUrl'];
    }

    public function getAuthor(): ?string {
        return $this->data['author'];
    }

    public function getOutletName(): string {
        return $this->data['outletName'];
    }
}
