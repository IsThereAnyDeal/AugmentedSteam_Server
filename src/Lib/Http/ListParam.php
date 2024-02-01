<?php
namespace AugmentedSteam\Server\Lib\Http;

use AugmentedSteam\Server\Exceptions\MissingParameterException;
use Psr\Http\Message\ServerRequestInterface;

class ListParam
{
    /** @var list<string> */
    private array $value;

    /**
     * @param list<string>|null $default
     */
    public function __construct(
        ServerRequestInterface $request,
        string $name,
        string $separator = ",",
        ?array $default = null,
        bool $nullable = false
    ) {
        $params = $request->getQueryParams();

        if (array_key_exists($name, $params)) {
            $this->value = explode($separator, $params[$name]);
        } else {
            if (is_null($default) && !$nullable) {
                throw new MissingParameterException($name);
            }

            $this->value = $default;
        }
    }

    /**
     * @return ?list<string>
     */
    public function value(): ?array {
        return $this->value;
    }
}
