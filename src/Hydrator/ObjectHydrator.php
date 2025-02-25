<?php

namespace PhpDevCommunity\RequestKit\Hydrator;

use LogicException;
use PhpDevCommunity\RequestKit\Type\ArrayOfType;
use PhpDevCommunity\RequestKit\Type\ItemType;
use ReflectionClass;

final class ObjectHydrator
{
    private string $object;
    private array $data;
    private array $definitions;

    public function __construct(string $object, array $data, array $definitions)
    {

        $this->object = $object;
        $this->data = $data;
        $this->definitions = $definitions;
    }

    public function hydrate(): object
    {
        return self::hydrateObject($this->object, $this->data, $this->definitions);
    }

    public static function hydrateObject(string $objectClass, array $data, array $definitions): object
    {
        if (!class_exists($objectClass)) {
            throw new LogicException('Class ' . $objectClass . ' does not exist');
        }

        $reflection = new ReflectionClass($objectClass);
        $object = $reflection->newInstance();
        foreach ($definitions as $key => $definition) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            $value = $data[$key];
            if (!$reflection->hasProperty($key)) {
                $key = self::snakeCaseToCamelCase($key);
            }
            $property = $reflection->getProperty($key);
            $property->setAccessible(true);
            if (is_array($value) && $definition instanceof ItemType) {
                $value = self::hydrateFromItemType($property, $definition, $value);
            }elseif (is_array($value) && $definition instanceof ArrayOfType) {
                $type = $definition->getCopyType();
                if ($type instanceof ItemType) {
                    $elements = [];
                    foreach ($value as $element) {
                        $elements[] = self::hydrateFromItemType($property, $type, $element);
                    }
                    $value = $elements;
                }
            }

            $property->setValue($object, $value);
        }

        return $object;
    }

    private static function hydrateFromItemType(?\ReflectionProperty $property, ItemType $definition, array $data): object
    {
        $propertyName = $property->getName();
        $propertyType = $property->getType();

        $objectToHydrate = $definition->getObject() ?: ($propertyType ? $propertyType->getName() : null);
        if ($objectToHydrate === null) {
            throw new LogicException('No object to hydrate, property ' . $propertyName . ' has no type and no object defined in the schema');
        }
        return self::hydrateObject($objectToHydrate, $data, $definition->copyDefinitions());
    }

    private static function snakeCaseToCamelCase(string $snakeCaseString): string
    {
        $camelCaseString = str_replace('_', '', ucwords($snakeCaseString, '_'));
        return lcfirst($camelCaseString);
    }

}
