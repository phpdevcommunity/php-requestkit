<?php

namespace PhpDevCommunity\RequestKit\Type;

use PhpDevCommunity\RequestKit\Type\Traits\StrictTrait;
use PhpDevCommunity\RequestKit\ValidationResult;
use PhpDevCommunity\Validator\Assert\Boolean;

final class BoolType extends AbstractType
{

    use StrictTrait;

    protected function validateValue(ValidationResult $result): void
    {
        if ($this->isStrict() && !is_bool($result->getValue())) {
            $result->setError("Value must be a boolean, got: " . gettype($result->getValue()));
            return;
        }

        if ($this->isStrict() === false && !is_bool($result->getValue())) {
            if (in_array($result->getValue(), [1, '1', 'true', 'on', 'TRUE', 'ON'], true)) {
                $result->setValue(true);
            }elseif (in_array($result->getValue(), [0, '0', 'false', 'off', 'FALSE', 'OFF'], true)) {
                $result->setValue(false);
            }else {
                $result->setError("Value must be a boolean, got: " . gettype($result->getValue()));
                return;
            }
        }

        $validator = new Boolean();
        if ($validator->validate($result->getValue()) === false) {
            $result->setError($validator->getError());
        }
    }
}
