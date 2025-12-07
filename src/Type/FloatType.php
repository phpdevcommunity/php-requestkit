<?php

namespace Depo\RequestKit\Type;

use Depo\RequestKit\Locale;
use Depo\RequestKit\Type\Traits\StrictTrait;
use Depo\RequestKit\ValidationResult;

final class FloatType extends AbstractType
{
    use StrictTrait;
    private ?float $min = null;
    private ?float $max = null;


    public function min(float $min): self
    {
        $this->min = $min;
        return $this;
    }

    public function max(float $max): self
    {
        $this->max = $max;
        return $this;
    }

    protected function validateValue(ValidationResult $result): void
    {
        if ($this->isStrict() && !is_float($result->getValue())) {
            $result->setError(Locale::get('error.type.float', ['type' => gettype($result->getValue())]));
            return;
        }

        if (!$this->isStrict() && !is_numeric($result->getValue())) {
            $result->setError(Locale::get('error.type.float', ['type' => gettype($result->getValue())]));
            return;
        }

        if (!$this->isStrict()) {
            $result->setValue(floatval($result->getValue()));
        }

        if ($this->min && $result->getValue() < $this->min) {
            $result->setError(Locale::get('error.int.min', ['min' => $this->min]));
            return;
        }

        if ($this->max && $result->getValue() > $this->max) {
            $result->setError(Locale::get('error.int.max', ['max' => $this->max]));
            return;
        }
    }
}
