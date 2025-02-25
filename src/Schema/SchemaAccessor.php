<?php

namespace PhpDevCommunity\RequestKit\Schema;

use InvalidArgumentException;

final class SchemaAccessor
{
    private \ArrayObject $data;

    public function __construct(array $data)
    {
     $this->data = new \ArrayObject($data);
    }

    public function get(string $key)
    {
        $current = $this->toArray();
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
        return $this->data->getIterator()->getArrayCopy();
    }
}
