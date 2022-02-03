<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DBadges extends AInsertableObject implements ISelectable
{
    protected int $id;
    protected string $title;
    protected string $img;

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
