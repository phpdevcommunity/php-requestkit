<?php

namespace PhpDevCommunity\RequestKit\Type;

use PhpDevCommunity\RequestKit\Type\Traits\StrictTrait;
use PhpDevCommunity\RequestKit\ValidationResult;
use PhpDevCommunity\Validator\Assert\Numeric;

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
            $result->setError("Value must be a float, got: " . gettype($result->getValue()));
            return;
        }

        if ($this->isStrict() === false && is_numeric($result->getValue())) {
            $value = floatval($result->getValue());
            $result->setValue($value);
        }

        $validator = new Numeric();
        if ($validator->validate($result->getValue()) === false) {
            $result->setError($validator->getError());
        }
    }
}
