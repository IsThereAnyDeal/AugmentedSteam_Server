<?php
namespace AugmentedSteam\Server\Http;

use AugmentedSteam\Server\Exceptions\InvalidValueException;
use AugmentedSteam\Server\Exceptions\MissingParameterException;
use Psr\Http\Message\ServerRequestInterface;

class IntParam
{
    private ?int $value;

    public function __construct(
        ServerRequestInterface $request,
        string $name,
        ?int $default = null,
        bool $nullable = false
    ) {
        $params = $request->getQueryParams();

        if (array_key_exists($name, $params)) {
            $value = $params[$name];

            if (!preg_match("#^[0-9]+$#", $value)) {
                throw new InvalidValueException($name);
            }
            $this->value = intval($value);
        } else {
            if (is_null($default) && !$nullable) {
                throw new MissingParameterException($name);
            }

            $this->value = $default;
        }
    }

    public function value(): ?int {
        return $this->value;
    }
}
