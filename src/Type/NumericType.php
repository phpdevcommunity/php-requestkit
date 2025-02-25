<?php

namespace PhpDevCommunity\RequestKit\Type;

use PhpDevCommunity\RequestKit\Type\Traits\StrictTrait;
use PhpDevCommunity\RequestKit\ValidationResult;
use PhpDevCommunity\Validator\Assert\Numeric;

final class NumericType extends AbstractType
{
    protected function validateValue(ValidationResult $result): void
    {
        if (is_numeric($result->getValue())) {
            $value = strval($result->getValue());
            $result->setValue($value);
        }

        $validator = new Numeric();
        if ($validator->validate($result->getValue()) === false) {
            $result->setError($validator->getError());
        }
    }
}
