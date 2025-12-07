<?php

namespace Depo\RequestKit\Schema;

use Depo\RequestKit\Exceptions\InvalidDataException;
use Depo\RequestKit\Locale;
use Depo\RequestKit\Type\AbstractType;
use Depo\RequestKit\Type\ArrayOfType;
use Depo\RequestKit\Type\ItemType;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractSchema
{
    protected bool $patchMode = false;
    protected string $title = '';
    protected string $version = '2.0';
    protected array $headerDefinitions = [];

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

    final public function withHeaders(array $definitions): self
    {
        $this->headerDefinitions = $definitions;
        return $this;
    }

    final public function processJsonInput(string $json, int $depth = 512, int $flags = 0): SchemaAccessor
    {
        $data = json_decode($json, true, $depth, $flags);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMessage = json_last_error_msg();
            throw new InvalidDataException(Locale::get('error.json', ['error' => $errorMessage]));
        }
        return $this->process($data);
    }

    final public function processHttpRequest(ServerRequestInterface $request): SchemaAccessor
    {
        $this->validateHeaders($request);

        if (in_array('application/json', $request->getHeader('Content-Type'))) {
            return $this->processJsonInput($request->getBody()->getContents());
        }

        return $this->processFormHttpRequest($request);
    }

    final public function processFormHttpRequest(ServerRequestInterface $request, ?string $expectedToken = null, string $csrfKey = '_csrf'): SchemaAccessor
    {
        $this->validateHeaders($request);
        $data = $request->getParsedBody();

        if ($expectedToken !== null) {
            if (!isset($data[$csrfKey]) || !hash_equals($expectedToken, $data[$csrfKey])) {
                throw new InvalidDataException(Locale::get('error.csrf'));
            }
            unset($data[$csrfKey]);
        }

        return $this->process($data);
    }

    final public function processHttpQuery(ServerRequestInterface $request): SchemaAccessor
    {
        return $this->process($request->getQueryParams(), true);
    }

    final public function process(array $data, bool $allowEmptyData = false): SchemaAccessor
    {
        $accessor = new SchemaAccessor($data, $this, $allowEmptyData);
        $accessor->execute();
        return $accessor;
    }

    abstract protected function definitions(): array;

    private function validateHeaders(ServerRequestInterface $request): void
    {
        if (empty($this->headerDefinitions)) {
            return;
        }

        $headerData = [];
        foreach ($request->getHeaders() as $name => $values) {
            $headerData[strtolower($name)] = $values[0] ?? null;
        }
        $headerDefinitions = [];
        foreach ($this->headerDefinitions as $name => $definition) {
            $headerDefinitions[strtolower($name)] = clone $definition;
        }

        $headerSchema = Schema::create($headerDefinitions);
        $headerSchema->process($headerData);
    }

    private function getDefinitions(): array
    {
        $definitions = $this->definitions();
        foreach ($definitions as $definition) {
            if (!$definition instanceof AbstractType) {
                throw new \InvalidArgumentException('Definition must be an instance of AbstractType');
            }
        }
        return $definitions;
    }

    final public function copyDefinitions(): array
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
            $data[$key] = $definition->getExample();
        }
        return $data;
    }
}
