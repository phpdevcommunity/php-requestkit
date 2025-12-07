<?php

namespace Depo\RequestKit\Type\Traits;

trait EqualTrait
{

    /**
     * @var mixed
     */
    protected $equalTo = null;
    protected bool $checkEquals = false;

    final public function equals($expectedValue)
    {
        $this->equalTo = $expectedValue;
        $this->checkEquals = true;
        return $this;
    }


}
