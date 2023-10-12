<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model\Component;

use Valantic\PimcoreApiDocumentationBundle\Enum\TypeEnum;

class ComponentPropertyDoc implements \JsonSerializable
{
    private string $name;
    private ?string $type = null;

    /** @var mixed[] */
    private array $items = [];
    private ?string $ref = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param mixed[] $items
     */
    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(?string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        $data = [];

        if ($this->getType() !== null) {
            $data['type'] = $this->getType();
        }

        if ($this->getItems() !== []) {
            $data['items'] = $this->getItems();
        } else {
            if ($this->getType() === TypeEnum::ARRAY->value) {
                $data['items'] = new \stdClass();
            }
        }

        if ($this->getRef() !== null) {
            $data['$ref'] = $this->getRef();
        }

        return $data;
    }
}
