<?php

namespace PhpDevCommunity\RequestKit\Type;

use PhpDevCommunity\RequestKit\Type\Traits\StrictTrait;
use PhpDevCommunity\RequestKit\ValidationResult;
use PhpDevCommunity\Validator\Assert\Integer;

final class IntType extends AbstractType
{
    use StrictTrait;

    private ?int $min = null;
    private ?int $max = null;

    public function min(int $min): self
    {
        $this->min = $min;
        return $this;
    }

    public function max(int $max): self
    {
        $this->max = $max;
        return $this;
    }

    protected function validateValue(ValidationResult $result): void
    {
        if ($this->isStrict() && !is_int($result->getValue())) {
            $result->setError("Value must be a int, got: " . gettype($result->getValue()));
            return;
        }

        if ($this->isStrict() === false && is_numeric($result->getValue())) {
            $value = intval($result->getValue());
            $result->setValue($value);
        }

        $validator = new Integer();
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
