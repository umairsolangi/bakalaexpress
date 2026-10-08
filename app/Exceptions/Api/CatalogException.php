<?php

namespace App\Exceptions\Api;

use Exception;

class CatalogException extends Exception
{
    protected string $errorCode;
    protected int $statusCode;

    public function __construct(string $message, string $errorCode = 'VALIDATION_ERROR', int $statusCode = 422)
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
