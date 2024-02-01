<?php
namespace AugmentedSteam\Server\Data\Objects;

class Players implements \JsonSerializable
{
    public int $current = 0;
    public int $peakToday = 0;
    public int $peakAll = 0;

    #[\Override]
    public function jsonSerialize(): array {
        return [
            "recent" => $this->current,
            "peak_today" => $this->peakToday,
            "peak_all" => $this->peakAll,
        ];
    }
}
