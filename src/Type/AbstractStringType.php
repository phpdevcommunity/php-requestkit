<?php

namespace Depo\RequestKit\Type;

use Depo\RequestKit\ValidationResult;
use PhpDevCommunity\Validator\Assert\StringLength;

abstract class AbstractStringType extends AbstractType
{

    public function padLeft(int $length, string $pad = ' '): self
    {
        $this->transform(function ($value) use ($length, $pad) {
            if (empty($value) || !is_string($value)) {
                return $value;
            }
            return str_pad($value, $length, $pad, STR_PAD_LEFT);
        });
        return $this;
    }

    public function padRight(int $length, string $pad = ' '): self
    {
        $this->transform(function ($value) use ($length, $pad) {
            if (empty($value) || !is_string($value)) {
                return $value;
            }
            return str_pad($value, $length, $pad, STR_PAD_RIGHT);
        });
        return $this;
    }

    public function removeSpaces(): self
    {
        $this->transform(function ($value) {
            if (empty($value) || !is_string($value)) {
                return $value;
            }
            return str_replace(' ', '', $value);
        });
        return $this;
    }

    public function removeChars(string ... $chars): self
    {
        $this->transform(function ($value) use ($chars) {
            if (empty($value) || !is_string($value)) {
                return $value;
            }
            return str_replace($chars, '', $value);
        });
        return $this;

    }

    public function trim(): self
    {
        $this->transform(function ($value) {
            if (empty($value) || !is_string($value)) {
                return $value;
            }
            return trim($value);
        });

        return $this;
    }

    public function uppercase(): self
    {
        $this->transform(function ($value) {
            if (empty($value) || !is_string($value)) {
                return $value;
            }
            return function_exists('mb_strtoupper') ? mb_strtoupper($value) : strtoupper($value);
        });
        return $this;
    }

    public function lowercase(): self
    {
        $this->transform(function ($value) {
            if (empty($value) || !is_string($value)) {
                return $value;
            }
            return function_exists('mb_strtoupper') ? mb_strtolower($value) : strtolower($value);
        });
        return $this;
    }
}
