<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

class DDlcCategories
{
    private int $id;
    private string $name;
    private string $icon;
    private string $description;

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): self {
        $this->id = $id;
        return $this;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function getIcon(): string {
        return $this->icon;
    }

    public function setIcon(string $icon): self {
        $this->icon = $icon;
        return $this;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description): self {
        $this->description = $description;
        return $this;
    }
}
