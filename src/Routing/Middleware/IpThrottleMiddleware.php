<?php
namespace AugmentedSteam\Server\Routing\Middleware;

use AugmentedSteam\Server\Lib\Redis\ERedisKey;
use AugmentedSteam\Server\Lib\Redis\RedisClient;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * IP-based throttling instead of App-based throttling
 */
class IpThrottleMiddleware implements MiddlewareInterface
{
    private const int WindowLength = 5*60;
    private const int Requests = 1000;

    public function __construct(
        private readonly RedisClient $redis
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $server = $request->getServerParams();
        $ip = $server['REMOTE_ADDR'];

        $key = ERedisKey::ApiThrottleIp->getKey($ip);
        $count = $this->redis->get($key);

        if (!is_null($count) && $count >= self::Requests) {
            $expireTime = $this->redis->expiretime($key);
            if ($expireTime > 0) {
                return new EmptyResponse(429, [
                    "Retry-After" => $expireTime - time()
                ]);
            }
        }
        $this->redis->incr($key);
        $this->redis->expire($key, self::WindowLength, "NX");

        return $handler->handle($request);
    }
}
