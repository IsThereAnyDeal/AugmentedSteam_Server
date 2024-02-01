<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

class DBadges
{
    private int $id;
    private string $title;
    private string $img;

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): self {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): self {
        $this->title = $title;
        return $this;
    }

    public function getImg(): string {
        return $this->img;
    }

    public function setImg(string $img): self {
        $this->img = $img;
        return $this;
    }
}
