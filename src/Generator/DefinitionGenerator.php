<?php

namespace PhpDevCommunity\RequestKit\Generator;

use PhpDevCommunity\RequestKit\Type;

final class DefinitionGenerator
{

    public function generateFromObject(string $objectClass)
    {
        $reflection = new \ReflectionClass($objectClass);
        $definitions = [];
        foreach ($reflection->getProperties() as $property) {
            $definition = Type::type();
            $definitions[$property->getName()] = $definition;
        }
        return $definitions;
    }

}
