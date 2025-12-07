<?php

namespace Depo\RequestKit\Type;

use Depo\RequestKit\Locale;
use Depo\RequestKit\Type\Traits\EqualTrait;
use Depo\RequestKit\Type\Traits\StrictTrait;
use Depo\RequestKit\ValidationResult;

final class IntType extends AbstractType
{
    use StrictTrait;
    use EqualTrait;

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
            $result->setError(Locale::get('error.type.int', ['type' => gettype($result->getValue())]));
            return;
        }

        if (!$this->isStrict() && !is_numeric($result->getValue())) {
            $result->setError(Locale::get('error.type.int', ['type' => gettype($result->getValue())]));
            return;
        }
        
        if (!$this->isStrict()) {
            $result->setValue(intval($result->getValue()));
        }

        if ($this->checkEquals && $result->getValue() !== $this->equalTo) {
            $result->setError(Locale::get('error.equals'));
            return;
        }

        if ($this->min !== null && $result->getValue() < $this->min) {
            $result->setError(Locale::get('error.int.min', ['min' => $this->min]));
            return;
        }

        if ($this->max !== null && $result->getValue() > $this->max) {
            $result->setError(Locale::get('error.int.max', ['max' => $this->max]));
            return;
        }
    }
}
