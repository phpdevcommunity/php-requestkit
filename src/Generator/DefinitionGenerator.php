<?php

namespace PhpDevCommunity\RequestKit\Generator;

use PhpDevCommunity\RequestKit\Type;

final class DefinitionGenerator
{

    public static function generateFromObject(string $objectClass): array
    {
        $reflection = new \ReflectionClass($objectClass);
        $definitions = [];

        foreach ($reflection->getProperties() as $property) {
            $type = $property->getType();
            $propertyName = self::camelCaseToSnakeCase($property->getName());
            $phpDoc = $property->getDocComment();
            $example = self::parsePhpDocTag($phpDoc, 'example')[0] ?? null;

            if ($type) {
                $name = $type->getName();
                if (in_array($name, ['array', 'iterable'], true)) {
                    $arrayType = self::extractArrayType(self::parsePhpDocTag($phpDoc, 'var')[0] ?? '', $property);
                    $definition = class_exists($arrayType)
                        ? Type::arrayOf(Type::itemObject($arrayType))
                        : Type::type('array_of_' . $arrayType);
                } else {
                    $definition = Type::type($name);
                }

                if (!$type->allowsNull() && !$definition instanceof Type\ArrayOfType) {
                    $definition->required();
                }
            } else {
                $definition = Type::string();
            }

            $definition->example($example);
            $definitions[$property->getName()] = $definition;
        }

        return $definitions;
    }

    private static function camelCaseToSnakeCase(string $camelCaseString): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $camelCaseString));
    }

    private static function parsePhpDocTag($phpDoc, string $tag): array {
        if (!is_string($phpDoc) || empty($phpDoc)) {
            return [];
        }

        $matches = [];
        $pattern = '/\*\s*@' . preg_quote($tag, '/') . '\s+([^\n]+)/';

        preg_match_all($pattern, $phpDoc, $matches);

        return $matches[1] ?? [];
    }

    public static function extractArrayType(string $type, \ReflectionProperty $property): ?string {
        if (preg_match('/array<([^>]+)>/', $type, $matches)) {
            $className = trim($matches[1]);

            if (class_exists($className)) {
                return $className;
            }

            $declaringClass = $property->getDeclaringClass();
            $namespace = $declaringClass->getNamespaceName();
            $fullClassName = $namespace ? "$namespace\\$className" : $className;

            return class_exists($fullClassName) ? $fullClassName : null;
        }
        return null;
    }
}
