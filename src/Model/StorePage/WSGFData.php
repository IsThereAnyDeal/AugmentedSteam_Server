<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\StorePage;

class WSGFData
{
    private array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function getTitle(): string {
        return $this->data['Title'];
    }

    public function getSteamId(): int {
        return (int)$this->data['SteamId'];
    }

    public function getPath(): string {
        return $this->data['Path'];
    }

    public function getWideScreenGrade(): string {
        return $this->data['WideScreenGrade'];
    }

    public function getMultiMonitorGrade(): string {
        return $this->data['MultiMonitorGrade'];
    }

    public function getUltraWideScreenGrade(): string {
        return $this->data['UltraWideScreenGrade'];
    }

    public function getGrade4k(): string {
        return $this->data['Grade4k'];
    }

    public function getNid(): int {
        return (int)$this->data['Nid'];
    }
}
