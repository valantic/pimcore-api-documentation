<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model\Doc;

class ResponseDoc implements \JsonSerializable
{
    private int $status;
    private string $description;

    /** @var mixed[] */
    private array $content = [];

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
