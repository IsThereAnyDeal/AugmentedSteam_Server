<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Exceptions;

class InvalidValueException extends ApiException {

    public function __construct(string $paramName) {
        parent::__construct("invalid_value", "Parameter '{$paramName}' has invalid value");
    }
}
