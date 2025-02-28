<?php

namespace PhpDevCommunity\RequestKit\Schema;

use PhpDevCommunity\RequestKit\Builder\SchemaObjectFactory;
use PhpDevCommunity\RequestKit\Generator\DefinitionGenerator;
use PhpDevCommunity\RequestKit\Type\AbstractType;
use ReflectionException;

final class Schema extends AbstractSchema
{
    /**
     * @var null|string|object
     */
    private $object = null;

    /**
     * @var AbstractType[]
     */
    private array $definitions;


    public static function create(array $definitions): Schema
    {
        return (new self())->setDefinitions($definitions);
    }

    /**
     * @param string|object $object
     * @param SchemaObjectFactory $factory
     * @return Schema
     */
    public static function createFromObject($object, SchemaObjectFactory $factory): Schema
    {
        return (new self())->generateDefinitionFromObject($object,$factory);
    }

    final public function extend(array $definitions): Schema
    {
        $schema = clone $this;
        $schema->setDefinitions($definitions + $this->definitions());
        return $schema;
    }

    /**
     * @param string|object $object
     * @param SchemaObjectFactory $factory
     * @return self
     */
    private function generateDefinitionFromObject($object, SchemaObjectFactory $factory): self
    {
        $definitionGenerator = new DefinitionGenerator($factory);
        $this->setDefinitions($definitionGenerator->generateFromObject($object));
        $this->object = $object;
        return $this;
    }

    private function setDefinitions(array $definitions): self
    {
        $this->definitions = $definitions;
        return $this;
    }

    public function getObject()
    {
        return $this->object;
    }

    protected function definitions(): array
    {
        return $this->definitions;
    }
}
