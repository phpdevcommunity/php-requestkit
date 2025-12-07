<?php

namespace Depo\RequestKit\Type;

use Depo\RequestKit\Locale;
use Depo\RequestKit\Type\Traits\EqualTrait;
use Depo\RequestKit\Type\Traits\StrictTrait;
use Depo\RequestKit\ValidationResult;

final class StringType extends AbstractStringType
{
    use StrictTrait;
    use EqualTrait;
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
            $result->setError(Locale::get('error.type.string', ['type' => gettype($result->getValue())]));
            return;
        }

        if (!$this->isStrict() && !is_string($result->getValue())) {
            if (!is_scalar($result->getValue())) {
                $result->setError(Locale::get('error.type.string', ['type' => gettype($result->getValue())]));
                return;
            }
            $result->setValue(strval($result->getValue()));
        }

        if ($this->checkEquals && $result->getValue() !== $this->equalTo) {
            $result->setError(Locale::get('error.equals'));
            return;
        }


        if (!empty($this->allowed) && !in_array($result->getValue(), $this->allowed, true)) {
            $result->setError(Locale::get('error.string.allowed', ['allowed' => implode(", ", $this->allowed)]));
            return;
        }

        $valueLength = function_exists('mb_strlen') ? mb_strlen($result->getValue()) : strlen($result->getValue());
        if ($this->min !== null && $valueLength < $this->min) {
            $result->setError(Locale::get('error.string.min_length', ['min' => $this->min]));
            return;
        }

        if ($this->max !== null && $valueLength > $this->max) {
            $result->setError(Locale::get('error.string.max_length', ['max' => $this->max]));
            return;
        }
    }
}
