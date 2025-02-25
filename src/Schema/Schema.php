<?php

namespace PhpDevCommunity\RequestKit\Schema;

use PhpDevCommunity\RequestKit\Type\AbstractType;

final class Schema extends AbstractSchema
{
    private ?string $object = null;

    /**
     * @var AbstractType[]
     */
    private array $definitions;

    private function __construct()
    {
    }

    public static function create(array $definitions): Schema
    {
        return (new self())->setDefinitions($definitions);
    }

    public static function createFromObject(string $object): Schema
    {
        return (new self())->setObject($object);
    }

    private function setObject(string $object): self
    {
        if (!class_exists($object)) {
            throw new \LogicException(sprintf('Class "%s" does not exist', $object));
        }
        $this->object = $object;
        return $this;
    }

    private function setDefinitions(array $definitions): self
    {
        $this->definitions = $definitions;
        return $this;
    }

     public function getObject() : ?string
    {
        return $this->object;
    }
    protected function definitions(): array
    {
        return $this->definitions;
    }
}
