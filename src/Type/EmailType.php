<?php

namespace PhpDevCommunity\RequestKit\Type;

use PhpDevCommunity\RequestKit\ValidationResult;
use PhpDevCommunity\Validator\Assert\Email;
use PhpDevCommunity\Validator\Assert\StringLength;

final class EmailType extends AbstractStringType
{
    protected function validateValue(ValidationResult $result): void
    {
        $validator = new Email();
        if ($validator->validate($result->getValue()) === false) {
            $result->setError($validator->getError());
        }
    }
}
