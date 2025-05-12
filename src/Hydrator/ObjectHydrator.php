<?php

namespace PhpDevCommunity\RequestKit\Hydrator;

use LogicException;
use PhpDevCommunity\RequestKit\Type\ArrayOfType;
use PhpDevCommunity\RequestKit\Type\ItemType;
use PhpDevCommunity\RequestKit\Type\MapType;
use PhpDevCommunity\RequestKit\Utils\KeyValueObject;
use ReflectionClass;

final class ObjectHydrator
{
    private $object;
    private array $data;
    private array $definitions;

    public function __construct($object, array $data, array $definitions)
    {
        $this->object = $object;
        $this->data = $data;
        $this->definitions = $definitions;
    }

    public function hydrate(): object
    {
        return self::hydrateObject($this->object, $this->data, $this->definitions);
    }
    private function hydrateObject($objectClass, array $data, array $definitions): object
    {
        if (is_object($objectClass)) {
            $object = $objectClass;
        }else {
            $object = new $objectClass();
        }

        $propertiesPublic = array_keys(get_class_vars(get_class($object)));
        foreach ($definitions as $key => $definition) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            $value = $data[$key];
            $propertyName = self::snakeCaseToCamelCase($key);

            if (is_array($value) && $definition instanceof ItemType) {
                $value = self::hydrateFromItemType( $definition, $value);
            }elseif (is_array($value) && $definition instanceof ArrayOfType) {
                $type = $definition->getCopyType();
                if ($type instanceof ItemType) {
                    $elements = [];
                    foreach ($value as $element) {
                        $elements[] = self::hydrateFromItemType($type, $element);
                    }
                    $value = $elements;
                }
            }
            if ($value instanceof KeyValueObject) {
                $value = $value->getArrayCopy();
            }
            if (in_array( $propertyName, $propertiesPublic)) {
                $object->$propertyName = $value;
            }elseif (method_exists($object, 'set' . $propertyName)) {
                $object->{'set' . $propertyName}($value);
            }else {
                throw new LogicException('Can not set property ' . $propertyName . ' on object ' . get_class($object));
            }
        }

        return $object;
    }

    private function hydrateFromItemType(ItemType $definition, array $data): object
    {
        $objectToHydrate = $definition->getObject();
        if ($objectToHydrate === null) {
            throw new LogicException('No object to hydrate, can not hydrate');
        }
        return $this->hydrateObject($objectToHydrate, $data, $definition->copyDefinitions());
    }

    private static function snakeCaseToCamelCase(string $snakeCaseString): string
    {
        $camelCaseString = str_replace('_', '', ucwords($snakeCaseString, '_'));
        return lcfirst($camelCaseString);
    }

}
