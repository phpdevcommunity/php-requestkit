<?php

namespace PhpDevCommunity\RequestKit\Schema;

use PhpDevCommunity\RequestKit\Type\AbstractType;

final class Schema extends AbstractSchema
{
    /**
     * @var AbstractType[]
     */
    private array $definitions;

    public function __construct(array $definitions)
    {
        $this->definitions = $definitions;
    }

    public static function create(array $definitions): Schema
    {
        return new self($definitions);
    }

    protected function definitions(): array
    {
        return $this->definitions;
    }
}
