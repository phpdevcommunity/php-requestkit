<?php

namespace PhpDevCommunity\RequestKit\Type;

use PhpDevCommunity\RequestKit\ValidationResult;
use PhpDevCommunity\Validator\Assert\Email;
use PhpDevCommunity\Validator\Assert\StringLength;

final class DateTimeType extends AbstractType
{
    private string $format = 'Y-m-d H:i:s';
    public function format(string $format): self
    {
        $this->format = $format;
        return $this;
    }

    protected function validateValue(ValidationResult $result): void
    {
        if (is_string($result->getValue())) {
            $datetime = \DateTime::createFromFormat($this->format, $result->getValue());
            if ($datetime === false) {
                $result->setError("Value must be a valid datetime for format: " . $this->format);
                return;
            }
            $result->setValue($datetime);
        }elseif (is_int($result->getValue())) {
            $datetime = new \DateTime();
            $datetime->setTimestamp($result->getValue());
            $result->setValue($datetime);
        }
    }
}
