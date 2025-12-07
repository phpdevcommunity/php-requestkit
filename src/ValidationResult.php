<?php

namespace Depo\RequestKit;

use Depo\RequestKit\Schema\SchemaAccessor;

final class ValidationResult
{
    private $rawValue;
    private array $values;
    private bool $isValid = true;
    private bool $isGlobal = true;
    private ?array $errors = null;
    private ?string $globalError = null;

    public function __construct($value)
    {
        $this->rawValue = $value;
        $this->values[] = $value;
    }

    /**
     * @return mixed
     */
    public function getRawValue()
    {
        return $this->rawValue;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        $values = $this->values;
        return end($values);
    }

    public function setValue($value)
    {
        $this->values[] = $value instanceof SchemaAccessor ? $value->toArray() : $value;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function isGlobalError(): bool
    {
        return $this->isGlobal;
    }

    public function getError(): ?string
    {
        $errors = $this->getErrors();
        if (empty($errors)) {
            return null;
        }
        return $this->globalError;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }
    public function setErrors(array $errors, bool $global = true) : self
    {
        if (empty($this->errors)) {
            $this->globalError = $errors[0] ?? null;
        }
        $this->errors = $errors;
        $this->isValid = false;
        $this->isGlobal = $global;
        return $this;
    }

    public function setError(string $error): self
    {
        return $this->setErrors([$error] , true);
    }
}
