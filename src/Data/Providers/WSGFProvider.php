<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Providers;

use AugmentedSteam\Server\Data\Interfaces\AppData\WSGFProviderInterface;
use AugmentedSteam\Server\Data\Objects\WSGF;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use AugmentedSteam\Server\Lib\Loader\SimpleLoader;

class WSGFProvider implements WSGFProviderInterface
{
    public function __construct(
        private readonly SimpleLoader $loader,
        private readonly EndpointBuilder $endpoints
    ) {}

    public function fetch(int $appid): ?WSGF {
        $endpoint = $this->endpoints->getWSGF($appid);
        $response = $this->loader->get($endpoint);

        if (!empty($response)) {
            $body = $response->getBody()->getContents();
            if (!empty($body)) {
                $json = json_decode($body, true, flags: JSON_THROW_ON_ERROR);

                if (is_array($json)) {
                    /**
                     * @var array{
                     *     url: string,
                     *     wide: string,
                     *     ultrawide: string,
                     *     multi_monitor: string,
                     *     "4k": string
                     * } $json
                     */

                    $wsgf = new WSGF();
                    $wsgf->path = $json['url'];
                    $wsgf->wideScreenGrade = $json['wide'];
                    $wsgf->ultraWideScreenGrade = $json['ultrawide'];
                    $wsgf->multiMonitorGrade = $json['multi_monitor'];
                    $wsgf->grade4k = $json['4k'];
                    return $wsgf;
                }
            }
        }

        return null;
    }
}
