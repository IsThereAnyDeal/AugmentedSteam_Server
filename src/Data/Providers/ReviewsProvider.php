<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Providers;

use AugmentedSteam\Server\Data\Interfaces\ReviewsProviderInterface;
use AugmentedSteam\Server\Data\Objects\Reviews\Review;
use AugmentedSteam\Server\Data\Objects\Reviews\Reviews;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use AugmentedSteam\Server\Loader\SimpleLoader;

class ReviewsProvider implements ReviewsProviderInterface
{
    public function __construct(
        private readonly SimpleLoader $loader,
        private readonly EndpointBuilder $endpoints
    ) {}

    public function fetch(int $appid): Reviews {
        $endpoint = $this->endpoints->getReviews($appid);
        $response = $this->loader->get($endpoint);

        $reviews = new Reviews();
        if (!is_null($response)) {
            $body = $response->getBody()->getContents();
            $json = json_decode($body, true, flags: JSON_THROW_ON_ERROR);

            if (is_array($json)) {
                if (!empty($json['metauser'])) {
                    $review = $json['metauser'];
                    $reviews->metauser = new Review(
                        $review['score'] ?? null,
                        $review['verdict'] ?? null,
                        $review['url']
                    );
                }

                if (!empty($json['opencritic'])) {
                    $review = $json['opencritic'];
                    $reviews->opencritic = new Review(
                        $review['score'] ?? null,
                        $review['verdict'] ?? null,
                        $review['url']
                    );
                }
            }
        }

        return $reviews;
    }
}
