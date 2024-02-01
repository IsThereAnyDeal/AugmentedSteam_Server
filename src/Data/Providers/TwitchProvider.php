<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Providers;

use AugmentedSteam\Server\Data\Interfaces\TwitchProviderInterface;
use AugmentedSteam\Server\Data\Objects\TwitchStream;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use AugmentedSteam\Server\Lib\Loader\SimpleLoader;

class TwitchProvider implements TwitchProviderInterface {

    public function __construct(
        private readonly SimpleLoader $loader,
        private readonly EndpointBuilder $endpoints
    ) {}

    public function fetch(string $channel): ?TwitchStream {
        $url = $this->endpoints->getTwitchStream($channel);

        $response = $this->loader->get($url);
        if (is_null($response)) {
            return null;
        }

        $body = $response->getBody()->getContents();

        /**
         * @var array{
         *     user_name: string,
         *     title: string,
         *     thumbnail_url: string,
         *     viewer_count: int,
         *     game: string
         * } $json
         */
        $json = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($json)) {
            return null;
        }

        $stream = new TwitchStream();
        $stream->userName = $json['user_name'];
        $stream->title = $json['title'];
        $stream->thumbnailUrl = $json['thumbnail_url'];
        $stream->viewerCount = $json['viewer_count'];
        $stream->game = $json['game'];
        return $stream;
    }
}
