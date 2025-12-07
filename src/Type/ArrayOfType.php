<?php

namespace Depo\RequestKit\Type;

use Depo\RequestKit\Exceptions\InvalidDataException;
use Depo\RequestKit\Locale;
use Depo\RequestKit\Schema\Schema;
use Depo\RequestKit\ValidationResult;

final class ArrayOfType extends AbstractType
{
    private AbstractType $type;

    private ?int $min = null;
    private ?int $max = null;
    private bool $acceptStringKeys = false;
    private bool $acceptCommaSeparatedValues = false;

    public function min(int $min): self
    {
        $this->min = $min;
        return $this;
    }

    public function max(int $max): self
    {
        $this->max = $max;
        return $this;
    }

    public function acceptStringKeys(): self
    {
        $this->acceptStringKeys = true;
        return $this;
    }

    public function acceptCommaSeparatedValues(): self
    {
        $this->acceptCommaSeparatedValues = true;
        return $this;
    }

    public function __construct(AbstractType $type)
    {
        $this->type = $type;
        $this->default([]);
    }

    public function getCopyType(): AbstractType
    {
        return clone $this->type;
    }

    protected function forceDefaultValue( ValidationResult $result): void
    {
        if ($result->getValue() === null) {
            $result->setValue([]);
        }
    }

    protected function validateValue(ValidationResult $result): void
    {
        if ($this->isRequired() && empty($this->min)) {
            $this->min = 1;
        }
        $values = $result->getValue();
        if (is_string($values) && $this->acceptCommaSeparatedValues) {
            $values = explode(',', $values);
            $values = array_map('trim', $values);
            $values = array_filter($values, fn($v) => $v !== '');
        }
        if (!is_array($values)) {
            $result->setError(Locale::get('error.type.array'));
            return;
        }

        $definitions = [];
        $count = count($values);
        if ($this->min && $count < $this->min) {
            $result->setError(Locale::get('error.array.min_items', ['min' => $this->min]));
            return;
        }
        if ($this->max && $count > $this->max) {
            $result->setError(Locale::get('error.array.max_items', ['max' => $this->max]));
            return;
        }

        foreach ($values as $key => $value) {
            if ($this->acceptStringKeys === false && !is_int($key)) {
                $result->setError(Locale::get('error.array.integer_keys'));
                return;
            }
            if (is_string($key)) {
                $key = trim($key);
            }
            $definitions[$key] = $this->type;
        }
        if (empty($definitions)) {
            $result->setValue([]);
            return;
        }

        $schema = Schema::create($definitions);
        try {
            $values = $schema->process($values);
        } catch (InvalidDataException $e) {
            $result->setErrors($e->getErrors(), false);
            return;
        }

        $result->setValue($values);
    }
}
