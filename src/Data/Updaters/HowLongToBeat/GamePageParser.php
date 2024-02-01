<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Updaters\HowLongToBeat;

use DOMDocument;
use DOMXPath;

class GamePageParser
{
    private function getTime(string $text): ?int {
        if (!preg_match("#(\d+)h(?:\s+(\d+)m)?#", $text, $m)) {
            return null;
        }

        $hours = ((int)$m[1])*60;
        $minutes = (int)($m[2] ?? 0);
        return $hours + $minutes;
    }

    /**
     * @return array{
     *     appid?: ?int,
     *     times?: array{
     *         main?: ?int,
     *         extra?: ?int,
     *         complete?: ?int
     *     }
     * }
     */
    public function parse(string $html): array {
        $dom = new DOMDocument();
        $dom->loadHTML($html, LIBXML_NOERROR);

        $xpath = new DOMXPath($dom);

        $platformNodes = $xpath->query(
            "//table[contains(@class, 'GamePlatformTable_game_main_table')]//td[1]/text()[contains(., 'Platform')]/.."
        );
        if ($platformNodes === false) {
            return [];
        }
        $platformNode = $platformNodes[0];

        $headNodes = $xpath->query("ancestor::tr//td", $platformNode);
        if ($headNodes === false) {
            return [];
        }

        $keys = [];
        $i = 0;
        foreach($headNodes as $node) {
            $keys[$i++] = trim($node->textContent);
        }

        $platformTables = $xpath->query("ancestor::table[contains(@class, 'GamePlatformTable_game_main_table')]", $platformNode);
        if ($platformTables === false) {
            return [];
        }
        $platformTable = $platformTables[0];

        $map = [];
        $pcRowNodes = $xpath->query("//td/text()[contains(., 'PC')]/ancestor::tr//td", $platformTable);
        if ($pcRowNodes === false) {
            return [];
        }

        $i = 0;
        foreach($pcRowNodes as $node) {
            $time = $this->getTime($node->textContent);
            if (!is_null($time)) {
                $map[$keys[$i]] = $time;
            }
            $i++;
        }

        $times = [];
        if (isset($map['Main'])) {
            $times['main'] = $map['Main'];
        }
        if (isset($map['Main +'])) {
            $times['extra'] = $map['Main +'];
        }
        if (isset($map['100%'])) {
            $times['complete'] = $map['100%'];
        }

        $appid = null;
        if (preg_match("#//store.steampowered.com/app/(\d+)/#", $html, $m)) {
            $appid = (int)$m[1];
        }

        return [
            "appid" => $appid,
            "times" => $times
        ];
    }
}
