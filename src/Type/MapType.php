<?php

namespace Depo\RequestKit\Type;

use Depo\RequestKit\Exceptions\InvalidDataException;
use Depo\RequestKit\Locale;
use Depo\RequestKit\Schema\Schema;
use Depo\RequestKit\Utils\KeyValueObject;
use Depo\RequestKit\ValidationResult;

final class MapType extends AbstractType
{
    private AbstractType $type;

    private ?int $min = null;
    private ?int $max = null;

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

    public function __construct(AbstractType $type)
    {
        $this->type = $type;
        $this->default(new KeyValueObject());
    }

    public function getCopyType(): AbstractType
    {
        return clone $this->type;
    }

    protected function forceDefaultValue(ValidationResult $result): void
    {
        if ($result->getValue() === null) {
            $result->setValue(new KeyValueObject());
        }
    }

    protected function validateValue(ValidationResult $result): void
    {
        if ($this->isRequired() && empty($this->min)) {
            $this->min = 1;
        }
        $values = $result->getValue();
        if (!is_array($values) && !$values instanceof KeyValueObject) {
            $result->setError(Locale::get('error.type.array'));
            return;
        }

        $count = count($values);
        if ($this->min !== null && $count < $this->min) {
            $result->setError(Locale::get('error.array.min_items', ['min' => $this->min]));
            return;
        }
        if ($this->max !== null && $count > $this->max) {
            $result->setError(Locale::get('error.array.max_items', ['max' => $this->max]));
            return;
        }

        $definitions = [];
        foreach ($values as $key => $value) {
            if (!is_string($key)) {
                $result->setError(Locale::get('error.map.string_key', ['key' => $key, 'type' => gettype($key)]));
                return;
            }
            $key = trim($key);
            $definitions[$key] = $this->type;
        }
        if (empty($definitions)) {
            $result->setValue(new KeyValueObject());
            return;
        }

        $schema = Schema::create($definitions);
        try {
            $values = $schema->process($values);
        } catch (InvalidDataException $e) {
            $result->setErrors($e->getErrors(), false);
            return;
        }

        $result->setValue(new KeyValueObject($values->toArray()));
    }
}
