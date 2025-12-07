<?php

namespace Depo\RequestKit\Builder;

use Depo\RequestKit\Schema\Schema;

final class SchemaObjectFactory
{
    private ?string $cacheDir;
    public function __construct(string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir;
    }

    public function createSchemaFromObject($object): Schema
    {
        return Schema::createFromObject($object, $this);
    }

    public function getCacheDir(): ?string
    {
        return $this->cacheDir;
    }
}
