<?php

namespace PhpDevCommunity\RequestKit\Type\Traits;

trait StrictTrait
{

    protected bool $strict = false;

    final public function strict()
    {
        $this->strict = true;
        return $this;
    }

    final public function notStrict()
    {
        $this->strict = false;
        return $this;
    }

    final protected function isStrict(): bool
    {
        return $this->strict;
    }
}
