<?php

namespace PhpDevCommunity\RequestKit\Type;

use PhpDevCommunity\RequestKit\Utils\DateOnly;
use PhpDevCommunity\RequestKit\ValidationResult;
use PhpDevCommunity\Validator\Assert\Email;
use PhpDevCommunity\Validator\Assert\StringLength;

final class DateType extends AbstractType
{
    private string $format = 'Y-m-d';
    public function format(string $format): self
    {
        $this->format = $format;
        return $this;
    }
    protected function validateValue(ValidationResult $result): void
    {
        if (is_string($result->getValue())) {
            $datetime = DateOnly::createFromFormat($this->format, $result->getValue());
            if ($datetime === false) {
                $result->setError("Value must be a valid date for format: " . $this->format);
                return;
            }
            $result->setValue($datetime);
        }elseif (is_int($result->getValue())) {
            $datetime = new DateOnly();
            $datetime->setTimestamp($result->getValue());
            $result->setValue($datetime);
        }
    }
}
