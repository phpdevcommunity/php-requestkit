<?php

namespace PhpDevCommunity\RequestKit\Schema;

use InvalidArgumentException;
use PhpDevCommunity\RequestKit\Exceptions\InvalidDataException;
use PhpDevCommunity\RequestKit\Hydrator\ObjectHydrator;
use PhpDevCommunity\RequestKit\Type\ItemType;

final class SchemaAccessor
{
    private array $initialData;
    private AbstractSchema $schema;
    private ?\ArrayObject $data = null;
    private bool $executed = false;

    private bool $allowEmptyData;

    public function __construct(array $initialData, Schema $schema, bool $allowEmptyData = false)
    {
        $this->initialData = $initialData;
        $this->schema = $schema;
        $this->allowEmptyData = $allowEmptyData;
    }

    public function execute(): void
    {
        $data = $this->initialData;
        if (empty($data) && $this->allowEmptyData === false) {
            throw new InvalidDataException('No data provided', 0);
        }

        $errors = [];
        $dataFiltered = [];
        foreach ($this->schema->copyDefinitions() as $key => $definition) {
            $aliases = array_merge([$key], $definition->getAliases());
            $keyToUse = null;
            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $data)) {
                    $keyToUse = $alias;
                    break;
                }
            }

            if ($this->schema->isPatchMode() && $keyToUse === null) {
                continue;
            }

            if ($this->schema->isPatchMode() && $definition instanceof ItemType) {
                $definition->patch();
            }

            if (array_key_exists( $keyToUse, $data)) {
                $value = $data[$keyToUse];
            }else {
                $value = $definition->getDefault();
            }

            $result = $definition->validate($value);
            if (!$result->isValid()) {
                if (!$result->isGlobalError()) {
                    $errors[$key] = $result->getErrors();
                } else {
                    $errors[$key] = $result->getError();
                }
                continue;
            }
            $dataFiltered[$key] = $result->getValue();
        }
        $errors = array_dot($errors);
        if (!empty($errors)) {
            throw new InvalidDataException('Validation failed', 0, $errors);
        }

        $this->data = new \ArrayObject($dataFiltered);
        $this->executed = true;
    }

    public function get(string $key)
    {
        if (!$this->executed) {
            throw new InvalidArgumentException('Schema not executed, call execute() first');
        }
        $current = $this->toArray();
        if (array_key_exists($key, $current)) {
            return $current[$key];
        }
        $pointer = strtok($key, '.');
        while ($pointer !== false) {
            if (!array_key_exists($pointer, $current)) {
                throw new InvalidArgumentException('Key ' . $key . ' not found');
            }
            $current = $current[$pointer];
            $pointer = strtok('.');
        }
        return $current;
    }

    public function toArray(): array
    {
        if (!$this->executed) {
            throw new InvalidArgumentException('Schema not executed, call execute() first');
        }

        return $this->data->getIterator()->getArrayCopy();
    }

    public function toObject(): object
    {
        if (!$this->executed) {
            throw new InvalidArgumentException('Schema not executed, call execute() first');
        }

        if ($this->schema->getObject() === null) {
            throw new InvalidArgumentException('Schema does not have an object, cannot hydrate');
        }

        return (new ObjectHydrator($this->schema->getObject(), $this->toArray(), $this->schema->copyDefinitions()))->hydrate();
    }

}
