<?php

namespace PhpDevCommunity\RequestKit\Type;

use Closure;
use PhpDevCommunity\RequestKit\ValidationResult;

abstract class AbstractType
{
    protected $example = null;
    protected bool $required = false;
    protected array $aliases = [];

    /**
     * @var mixed
     */
    protected $default = null;
    protected ?array $transformers = null;


    final public function required(): self
    {
        $this->required = true;
        return $this;
    }

    final public function optional(): self
    {
        $this->required = false;
        return $this;
    }

    final public function default($value): self
    {
        $this->default = $value;
        return $this;
    }

    final public function alias(string ...$aliases): self
    {
        foreach ($aliases as $alias) {
            $this->aliases[$alias] = $alias;
        }
        return $this;
    }

    final public function example($value): self
    {
        $this->example = $value;
        return $this;
    }

    final public function transform(Closure $transform): self
    {
        $this->transformers[] = $transform;
        return $this;
    }

    final protected function isRequired(): bool
    {
        return $this->required;
    }

    final  public function getDefault()
    {
        $default = $this->default;
        if (is_callable($default)) {
            $default = $default();
        }
        return $default;
    }

    final public function getAliases(): array
    {
        return $this->aliases;
    }

    final public function getExample()
    {
        return $this->example;
    }

    final protected function transformValue(ValidationResult $result): void
    {
        if (empty($this->transformers)) {
            return;
        }
        foreach ($this->transformers as $transformer) {
            $value = $result->getValue();
            $value = $transformer($value);
            $result->setValue($value);
        }
    }

    final public function validate($value): ValidationResult
    {
        $result = new ValidationResult($value);
        $this->forceDefaultValue($result);

        if ($result->getValue() === null || (is_string($result->getValue())) && trim($result->getValue()) === '') {
            if ($this->isRequired()) {
                $result->setError("Value is required, but got null or empty string");
            }
            return $result;
        }

        $this->transformValue($result);
        $this->validateValue($result);

        return $result;
    }

    abstract protected function validateValue(ValidationResult $result): void;

    protected function forceDefaultValue(ValidationResult $result): void
    {
    }
}
