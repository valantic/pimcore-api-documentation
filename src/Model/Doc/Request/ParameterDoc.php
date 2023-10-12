<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model\Doc\Request;

class ParameterDoc implements \JsonSerializable
{
    final public const IN_PATH = 'path';
    final public const IN_QUERY = 'query';
    private string $name;
    private string $in;
    private bool $required;

    /** @var mixed[] */
    private array $schema;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIn(): string
    {
        return $this->in;
    }

    public function setIn(string $in): self
    {
        $this->in = $in;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getSchema(): array
    {
        return $this->schema;
    }

    /**
     * @param mixed[] $schema
     */
    public function setSchema(array $schema): self
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->getName(),
            'in' => $this->getIn(),
            'required' => $this->isRequired(),
            'schema' => $this->getSchema(),
        ];
    }
}
