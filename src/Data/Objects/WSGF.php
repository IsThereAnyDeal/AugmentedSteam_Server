<?php
namespace AugmentedSteam\Server\Data\Objects;

class WSGF implements \JsonSerializable
{
    public string $title;
    public int $steamId;
    public string $path;
    public string $wideScreenGrade;
    public string $multiMonitorGrade;
    public string $ultraWideScreenGrade;
    public string $grade4k;
    public int $nid;

    #[\Override]
    public function jsonSerialize(): array {
        return [
            "url" => $this->path,
            "wide" => $this->wideScreenGrade,
            "ultrawide" => $this->ultraWideScreenGrade,
            "multi_monitor" => $this->multiMonitorGrade,
            "4k" => $this->grade4k,
        ];
    }
}
