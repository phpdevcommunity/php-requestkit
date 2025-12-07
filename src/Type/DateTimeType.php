<?php

namespace Depo\RequestKit\Type;

use Depo\RequestKit\Locale;
use Depo\RequestKit\ValidationResult;

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
        $value = $result->getValue();
        if ($value instanceof \DateTimeInterface) {
            return;
        }

        if (is_string($value)) {
            $datetime = \DateTime::createFromFormat($this->format, $value);
            if ($datetime === false || $datetime->format($this->format) !== $value) {
                $result->setError(Locale::get('error.type.datetime'));
                return;
            }
            $result->setValue($datetime);
        } elseif (is_int($value)) {
            $datetime = new \DateTime();
            $datetime->setTimestamp($value);
            $result->setValue($datetime);
        } else {
            $result->setError(Locale::get('error.type.datetime'));
        }
    }
}
