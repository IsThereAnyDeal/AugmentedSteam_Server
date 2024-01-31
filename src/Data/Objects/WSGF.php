<?php
namespace AugmentedSteam\Server\Data\Objects;

class WSGF
{
    public string $title;
    public int $steamId;
    public string $path;
    public string $wideScreenGrade;
    public string $multiMonitorGrade;
    public string $ultraWideScreenGrade;
    public string $grade4k;
    public int $nid;

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            "Title" => $this->title,
            "SteamID" => $this->steamId,
            "Path" => $this->path,
            "WideScreenGrade" => $this->wideScreenGrade,
            "MultiMonitorGrade" => $this->multiMonitorGrade,
            "UltraWideScreenGrade" => $this->ultraWideScreenGrade,
            "Grade4k" => $this->grade4k,
            "Nid" => $this->nid
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function fromArray(array $data): self {
        $this->title = $data['Title'];
        $this->steamId = $data['SteamID'];
        $this->path = $data['Path'];
        $this->wideScreenGrade = $data['WideScreenGrade'];
        $this->multiMonitorGrade = $data['MultiMonitorGrade'];
        $this->ultraWideScreenGrade = $data['UltraWideScreenGrade'];
        $this->grade4k = $data['Grade4k'];
        $this->nid = $data['Nid'];
        return $this;
    }
}
