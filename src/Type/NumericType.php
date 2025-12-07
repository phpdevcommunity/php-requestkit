<?php

namespace Depo\RequestKit\Type;

use Depo\RequestKit\Locale;
use Depo\RequestKit\Type\Traits\EqualTrait;
use Depo\RequestKit\ValidationResult;

final class NumericType extends AbstractType
{
    use EqualTrait;

    protected function validateValue(ValidationResult $result): void
    {
        if (!is_numeric($result->getValue())) {
            $result->setError(Locale::get('error.type.numeric', ['type' => gettype($result->getValue())]));
            return;
        }

        $result->setValue(strval($result->getValue()));

        if ($this->checkEquals && $result->getValue() !== $this->equalTo) {
            $result->setError(Locale::get('error.equals'));
        }
    }
}
