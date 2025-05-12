<?php

namespace PhpDevCommunity\RequestKit\Schema;

use Exception;
use PhpDevCommunity\RequestKit\Exceptions\InvalidDataException;
use PhpDevCommunity\RequestKit\Type\AbstractType;
use PhpDevCommunity\RequestKit\Type\ArrayOfType;
use PhpDevCommunity\RequestKit\Type\ItemType;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractSchema
{
    protected bool $patchMode = false;
    protected string $title = '';
    protected string $version = '1.0';

    final public function patch(): self
    {
        $this->patchMode = true;
        return $this;
    }

    final public function isPatchMode(): bool
    {
        return $this->patchMode;
    }

    final public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    final public function version(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @param string $json
     * @param int $depth
     * @param int $flags
     * @return SchemaAccessor
     * @throws InvalidDataException
     */
    final public function processJsonInput(string $json, int $depth = 512, int $flags = 0): SchemaAccessor
    {
        $data = json_decode($json, true, $depth , $flags);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMessage = json_last_error_msg();
            throw new InvalidDataException($errorMessage);
        }
        return $this->process($data);
    }

    /**
     * @param ServerRequestInterface $request
     * @return SchemaAccessor
     * @throws InvalidDataException
     */
    final public function processHttpRequest(ServerRequestInterface $request): SchemaAccessor
    {
        if (in_array('application/json', $request->getHeader('Content-Type'))) {
            return $this->processJsonInput($request->getBody()->getContents());
        }
        return $this->process($request->getParsedBody());
    }

    /**
     * @param ServerRequestInterface $request
     * @return SchemaAccessor
     * @throws InvalidDataException
     */
    final public function processHttpQuery(ServerRequestInterface $request): SchemaAccessor
    {
        return $this->process($request->getQueryParams(), true);
    }

    /**
     * @param array $data
     * @param bool $allowEmptyData
     * @return SchemaAccessor
     * @throws InvalidDataException
     */
    final public function process(array $data, bool $allowEmptyData = false): SchemaAccessor
    {
        $accessor = new SchemaAccessor($data, $this, $allowEmptyData);
        $accessor->execute();
        return $accessor;
    }

    /**
     * @return array<string, AbstractType>
     */
    abstract protected function definitions(): array;

    final private function getDefinitions(): array
    {
        $definitions = $this->definitions();
        foreach ($definitions as $definition) {
            if (!$definition instanceof AbstractType) {
                throw new \InvalidArgumentException('Definition must be an instance of AbstractType');
            }
        }
        return $definitions;
    }

    /**
     * @return array<string, AbstractType>
     */
    final public function copyDefinitions() : array
    {
        $definitions = [];
        foreach ($this->getDefinitions() as $key => $definition) {
            $definitions[$key] = clone $definition;
        }
        return $definitions;
    }

    final public function generateExampleData(): array
    {
        $data = [];
        foreach ($this->getDefinitions() as $key => $definition) {
            if ($definition instanceof ItemType) {
                $data[$key] = $definition->getExample() ?: $definition->copySchema()->generateExampleData();
                continue;
            }
            if ($definition instanceof ArrayOfType) {
                $data[$key][] = $definition->getExample();
                continue;
            }
            $data[$key] = $definition->getExample();
        }
        return $data;
    }
}
