<?php

namespace Depo\RequestKit\Type;

use Depo\RequestKit\Locale;
use Depo\RequestKit\ValidationResult;

final class EmailType extends AbstractStringType
{
    protected function validateValue(ValidationResult $result): void
    {
        if (filter_var($result->getValue(), FILTER_VALIDATE_EMAIL) === false) {
            $result->setError(Locale::get('error.string.email'));
        }
    }
}
