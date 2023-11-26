<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model\Doc;

use Valantic\PimcoreApiDocumentationBundle\Model\Component\ComponentSchemaDoc;

class ResponseDoc implements \JsonSerializable
{
    private int $status;
    private string $description;

    /** @var ComponentSchemaDoc[] */
    private array $componentSchemas = [];

    /** @var mixed[] */
    private array $content = [];

    /**
     * @return ComponentSchemaDoc[]
     */
    public function getComponentSchemas(): array
    {
        return $this->componentSchemas;
    }

    /**
     * @param ComponentSchemaDoc[] $componentSchemas
     */
    public function setComponentSchemas(array $componentSchemas): ResponseDoc
    {
        $this->componentSchemas = $componentSchemas;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @param mixed[] $content
     */
    public function setContent(array $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        $data = [
            'description' => $this->getDescription(),
        ];

        if ($this->getContent() !== []) {
            $data['content'] = $this->getContent();
        }

        return $data;
    }
}
