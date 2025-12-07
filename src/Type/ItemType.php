<?php

namespace Depo\RequestKit\Type;

use Depo\RequestKit\Exceptions\InvalidDataException;
use Depo\RequestKit\Locale;
use Depo\RequestKit\Schema\Schema;
use Depo\RequestKit\ValidationResult;

final class ItemType extends AbstractType
{
    private Schema $schema;

    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    public function patch(): self
    {
        $this->schema->patch();
        return $this;
    }

    public function getObject() : ?string
    {
        return $this->schema->getObject();
    }

    public function copyDefinitions(): array
    {
        return $this->schema->copyDefinitions();
    }

    public function copySchema(): Schema
    {
        return clone $this->schema;
    }

    protected function validateValue(ValidationResult $result): void
    {
        $value = $result->getValue();
        if (!is_array($value)) {
            $result->setError(Locale::get('error.type.array'));
            return;
        }
        try {
            $result->setValue($this->schema->process($value));
        } catch (InvalidDataException $e) {
            $result->setErrors($e->getErrors(), false);
        }
    }
}
