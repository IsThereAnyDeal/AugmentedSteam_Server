<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Exceptions;

use League\Route\Http\Exception\BadRequestException;

class ApiException extends BadRequestException {

    private string $errorCode;
    private string $errorMessage;

    public function __construct(string $errorCode, string $errorMessage) {
        parent::__construct();

        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    function getErrorCode(): string {
        return $this->errorCode;
    }

    function getErrorMessage(): string {
        return $this->errorMessage;
    }
}
