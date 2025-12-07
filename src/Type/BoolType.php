<?php

namespace Depo\RequestKit\Type;

use Depo\RequestKit\Locale;
use Depo\RequestKit\Type\Traits\EqualTrait;
use Depo\RequestKit\Type\Traits\StrictTrait;
use Depo\RequestKit\ValidationResult;

final class BoolType extends AbstractType
{
    use StrictTrait;
    use EqualTrait;

    protected function validateValue(ValidationResult $result): void
    {
        $value = $result->getValue();

        if ($this->isStrict() && !is_bool($value)) {
            $result->setError(Locale::get('error.type.bool', ['type' => gettype($value)]));
            return;
        }

        if ($this->isStrict() === false && !is_bool($value)) {
            if (in_array($value, [1, '1', 'true', 'on', 'TRUE', 'ON'], true)) {
                $result->setValue(true);
            } elseif (in_array($value, [0, '0', 'false', 'off', 'FALSE', 'OFF'], true)) {
                $result->setValue(false);
            } else {
                $result->setError(Locale::get('error.type.bool', ['type' => gettype($value)]));
                return;
            }
        }

        if ($this->checkEquals && $result->getValue() !== $this->equalTo) {
            $result->setError(Locale::get('error.equals'));
        }
    }
}
