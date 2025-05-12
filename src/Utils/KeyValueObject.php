<?php

namespace PhpDevCommunity\RequestKit\Utils;

final class KeyValueObject extends \ArrayObject implements \JsonSerializable
{
    public function jsonSerialize() : object
    {
        return (object)$this->getArrayCopy();
    }
}
