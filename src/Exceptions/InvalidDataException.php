<?php

namespace PhpDevCommunity\RequestKit\Exceptions;

class InvalidDataException extends \Exception
{
    private array $errors;

    public function __construct(string $message = 'Invalid request data', int $code = 400, array $errors = [])
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getError(string $key): ?string
    {
        return $this->errors[$key] ?? null;
    }

    public function toResponse(): array
    {
        return [
            'status' => 'error',
            'message' => $this->getMessage(),
            'errors' => $this->getErrors(),
        ];
    }
}
