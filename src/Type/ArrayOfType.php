<?php

namespace PhpDevCommunity\RequestKit\Type;

use PhpDevCommunity\RequestKit\Exceptions\InvalidDataException;
use PhpDevCommunity\RequestKit\Schema\Schema;
use PhpDevCommunity\RequestKit\ValidationResult;

final class ArrayOfType extends AbstractType
{
    private AbstractType $type;

    private ?int $min = null;
    private ?int $max = null;
    private ?bool $acceptStringKeys = false;

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
        if (!is_array($values)) {
            $result->setError('Value must be an array');
            return;
        }

        $definitions = [];
        $count = count($values);
        if ($this->min && $count < $this->min) {
            $result->setError("Value must have at least $this->min item(s)");
            return;
        }
        if ($this->max && $count > $this->max) {
            $result->setError("Value must have at most $this->max item(s)");
            return;
        }

        foreach ($values as $key => $value) {
            if ($this->acceptStringKeys === false && !is_int($key)) {
                $result->setError('All keys must be integers');
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
