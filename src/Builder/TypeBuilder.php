<?php

namespace Depo\RequestKit\Builder;

use Depo\RequestKit\Type;
use Depo\RequestKit\Type\AbstractType;

final class TypeBuilder
{
    private ?string $cacheDir;
    public function __construct(string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir;
    }

    public function build(string $type): AbstractType
    {
        return Type::typeObject($type, $this->cacheDir) ?? Type::type($type);
    }
}
