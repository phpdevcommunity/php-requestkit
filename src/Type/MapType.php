<?php

namespace PhpDevCommunity\RequestKit\Type;

use PhpDevCommunity\RequestKit\Exceptions\InvalidDataException;
use PhpDevCommunity\RequestKit\Schema\Schema;
use PhpDevCommunity\RequestKit\Utils\KeyValueObject;
use PhpDevCommunity\RequestKit\ValidationResult;

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
            $result->setError('Value must be an array or KeyValueObject');
            return;
        }

        $count = count($values);
        if ($this->min && $count < $this->min) {
            $result->setError("Value must have at least $this->min item(s)");
            return;
        }
        if ($this->max && $count > $this->max) {
            $result->setError("Value must have at most $this->max item(s)");
            return;
        }

        $definitions = [];
        foreach ($values as $key => $value) {
            if (!is_string($key)) {
                $result->setError(sprintf( 'Key "%s" must be a string, got %s', $key, gettype($key)));
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
