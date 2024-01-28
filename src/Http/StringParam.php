<?php
namespace AugmentedSteam\Server\Http;

use AugmentedSteam\Server\Exceptions\InvalidValueException;
use AugmentedSteam\Server\Exceptions\MissingParameterException;
use Psr\Http\Message\ServerRequestInterface;

class StringParam
{
    private ?string $value;

    public function __construct(
        ServerRequestInterface $request,
        string $name,
        ?string $default = null,
        bool $nullable = false
    ) {
        $params = $request->getQueryParams();

        if (array_key_exists($name, $params)) {
            $value = trim($params[$name]);

            if (empty($value)) {
                throw new InvalidValueException($name);
            }
            $this->value = $value;
        } else {
            if (is_null($default) && !$nullable) {
                throw new MissingParameterException($name);
            }

            $this->value = $default;
        }
    }

    public function value(): ?string {
        return $this->value;
    }
}
