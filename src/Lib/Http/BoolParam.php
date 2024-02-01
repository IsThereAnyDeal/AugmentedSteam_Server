<?php
namespace AugmentedSteam\Server\Lib\Http;

use AugmentedSteam\Server\Exceptions\InvalidValueException;
use AugmentedSteam\Server\Exceptions\MissingParameterException;
use Psr\Http\Message\ServerRequestInterface;

class BoolParam
{
    private ?bool $value;

    public function __construct(
        ServerRequestInterface $request,
        string $name,
        ?bool $default = null,
        bool $nullable = false
    ) {
        $params = $request->getQueryParams();

        if (array_key_exists($name, $params)) {
            $this->value = match($params[$name]) {
                "1", "true" => true,
                "0", "false" => false,
                default => throw new InvalidValueException($name)
            };
        } else {
            if (is_null($default) && !$nullable) {
                throw new MissingParameterException($name);
            }

            $this->value = $default;
        }
    }

    public function value(): ?bool {
        return $this->value;
    }
}
