<?php

namespace PhpDevCommunity\RequestKit\Type;

use PhpDevCommunity\RequestKit\Exceptions\InvalidDataException;
use PhpDevCommunity\RequestKit\Schema\Schema;
use PhpDevCommunity\RequestKit\ValidationResult;

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
        try {
            $result->setValue($this->schema->process($result->getValue()));
        } catch (InvalidDataException $e) {
            $result->setErrors($e->getErrors(), false);
        }
    }
}
