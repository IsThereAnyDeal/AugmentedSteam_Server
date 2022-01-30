<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Exceptions;

class MissingParameterException extends ApiException {

    public function __construct(string $missingParamName) {
        parent::__construct("missing_param", "Required parameter '{$missingParamName}' is missing");
    }
}
