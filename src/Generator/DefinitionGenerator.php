<?php

namespace PhpDevCommunity\RequestKit\Generator;

use PhpDevCommunity\RequestKit\Builder\SchemaObjectFactory;
use PhpDevCommunity\RequestKit\Type;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

final class DefinitionGenerator
{
    private SchemaObjectFactory $factory;
    public function __construct(SchemaObjectFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param string|object $objectClass
     * @return array
     */
    public function generateFromObject($objectClass): array
    {
        if (is_object($objectClass)) {
            $object = $objectClass;
        }else {
            $object = new $objectClass();
        }

        $metadata = $this->cacheGet($object);
        if (!empty($metadata)) {
            return $this->generateFromMetadata($object, $metadata);
        }

        $reflection = new ReflectionClass($object);
        $metadata['object_class'] = get_class($object);
        $metadata['php_class'] = $reflection->getExtensionName() === false;
        $metadata['properties'] = [];

        foreach ($reflection->getProperties() as $property) {

            $type = $property->getType();
            $propertyName = self::camelCaseToSnakeCase($property->getName());
            $phpDoc = $property->getDocComment();
            $example = self::parsePhpDocTag($phpDoc, 'example')[0] ?? null;
            $required = false;

            if ($type) {
                $name = $type->getName();
                $propertyType = $name;
                if (in_array($name, ['array', 'iterable'], true)) {
                    $arrayType = self::extractArrayType(self::parsePhpDocTag($phpDoc, 'var')[0] ?? '', $property);
                    $propertyType = class_exists($arrayType) ? "array_of_item:$arrayType" : "array_of_$arrayType";
                }

                if (!$type->allowsNull() && !str_starts_with($propertyType, 'array_of_')) {
                    $required = true;
                }
            } else {
                $propertyType = 'string';
            }

            $metadata['properties'][$propertyName]['type'] = $propertyType;
            $metadata['properties'][$propertyName]['public'] = $property->isPublic();
            $metadata['properties'][$propertyName]['name'] = $property->getName();
            $metadata['properties'][$propertyName]['required'] = $required;
            $metadata['properties'][$propertyName]['example'] = $example;
        }

        $this->cacheSet($object, $metadata);
        return $this->generateFromMetadata($object, $metadata);
    }

    private function generateFromMetadata(object $object, array $metadata): array
    {
        $definitions = [];
        foreach ($metadata['properties'] as $name => $property) {
            $type = $property['type'];
            $example = $property['example'];
            $propertyName = $property['name'];
            $required = $property['required'];
            $defaultValue = null;

            if ($property['public'] && isset($object->$propertyName)) {
                $defaultValue = $object->$propertyName;
            } elseif (method_exists($object, 'get' . ucfirst($propertyName))) {
                $defaultValue = $object->{'get' . ucfirst($propertyName)}();
            }elseif (method_exists($object, 'is' . ucfirst($propertyName))) {
                $defaultValue = $object->{'is' . ucfirst($propertyName)}();
            }

            if (str_starts_with( $type, 'array_of_item:')) {
                $class = substr($type, 14);
                $definitionType =  Type::typeObject($class);
                if ($definitionType === null) {
                    $definitionType = new Type\ItemType($this->factory->createSchemaFromObject($class));
                }
                $definition = Type::arrayOf($definitionType);
            }elseif (class_exists($type)) {
                $definition =  Type::typeObject($type);
                if ($definition === null) {
                    $definition = new Type\ItemType($this->factory->createSchemaFromObject($type));
                }
            } else {
                $definition = Type::type($type);
            }

            $definition->example($example);
            $definition->default($defaultValue);
            if ($required) {
                $definition->required();
            }
            $definitions[$name] = $definition;
        }

        return $definitions;
    }

    private function cacheGet(object $object)
    {
        $key = md5(get_class($object));
        if ($this->factory->getCacheDir()) {
            $file = $this->factory->getCacheDir() . DIRECTORY_SEPARATOR . $key . '.definition.json';
            if (file_exists($file)) {
                return unserialize(file_get_contents($file));
            }
        }
        return [];
    }

    private function cacheSet(object $object, array $metadata): void
    {
        $key = md5(get_class($object));
        if ($this->factory->getCacheDir()) {
            $file = $this->factory->getCacheDir() . DIRECTORY_SEPARATOR . $key . '.definition.json';
            file_put_contents($file, serialize($metadata));
        }
    }

    private static function camelCaseToSnakeCase(string $camelCaseString): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $camelCaseString));
    }

    private static function parsePhpDocTag($phpDoc, string $tag): array
    {
        if (!is_string($phpDoc) || empty($phpDoc)) {
            return [];
        }

        $matches = [];
        $pattern = '/\*\s*@' . preg_quote($tag, '/') . '\s+([^\n]+)/';

        preg_match_all($pattern, $phpDoc, $matches);

        return $matches[1] ?? [];
    }

    private static function extractArrayType(string $type, ReflectionProperty $property): ?string
    {
        if (preg_match('/array<([^>]+)>/', $type, $matches)) {
            $typeParsed = trim($matches[1]);
            if (self::isNativeType($typeParsed)) {
                return $typeParsed;
            }

            $classname = $typeParsed;
            if (class_exists($classname)) {
                return $classname;
            }

            $declaringClass = $property->getDeclaringClass();
            $namespace = $declaringClass->getNamespaceName();
            $fullClassName = $namespace ? "$namespace\\$classname" : $classname;

            return class_exists($fullClassName) ? $fullClassName : null;
        }
        return null;
    }

    private static function isNativeType(string $type): bool
    {
        $nativeTypes = [
            'int', 'integer',
            'float', 'double',
            'string',
            'bool', 'boolean',
            'array',
            'object',
            'callable',
            'iterable',
            'resource',
            'null',
        ];
        return in_array($type, $nativeTypes, true);
    }

}
