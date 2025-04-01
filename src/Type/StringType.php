<?php

namespace PhpDevCommunity\RequestKit\Type;

use PhpDevCommunity\RequestKit\Type\Traits\StrictTrait;
use PhpDevCommunity\RequestKit\ValidationResult;
use PhpDevCommunity\Validator\Assert\StringLength;

final class StringType extends AbstractStringType
{
    use StrictTrait;
    private array $allowed = [];
    private ?int $min = null;
    private ?int $max = null;

    public function allowed(string ...$allowed): self
    {
        $this->allowed = $allowed;
        return $this;
    }

    public function length(int $min, ?int $max = null): self
    {
        $this->min = $min;
        $this->max = $max;
        return $this;
    }

    protected function validateValue(ValidationResult $result): void
    {
        if ($this->isStrict() && !is_string($result->getValue())) {
            $result->setError("Value must be a string, got: " . gettype($result->getValue()));
            return;
        }

        if ($this->isStrict() === false && !is_string($result->getValue())) {

            if (is_array($result->getValue())) {
                $result->setError("Value must be a string, got: array");
                return;
            }

            $value = strval($result->getValue());
            $result->setValue($value);
        }

        if (!empty($this->allowed) && !in_array($result->getValue(), $this->allowed, $this->isStrict())) {
            $result->setError("Value is not allowed, allowed values are: " . implode(", ", $this->allowed));
            return;
        }

        $validator = new StringLength();
        if ($this->min) {
            $validator->min($this->min);
        }
        if ($this->max) {
            $validator->max($this->max);
        }
        if ($validator->validate($result->getValue()) === false) {
            $result->setError($validator->getError());
        }
    }
}
