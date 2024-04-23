<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model\Component\Property;

use Valantic\PimcoreApiDocumentationBundle\Contract\Model\Component\Property\ComponentSchemaPropertyInterface;
use Valantic\PimcoreApiDocumentationBundle\Enum\TypeEnum;

class ArrayPropertyDoc extends AbstractPropertyDoc implements ComponentSchemaPropertyInterface
{
    use ComponentSchemaPropertyTrait;

    /**
     * @var mixed[]
     */
    protected array $items = [];

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

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        $data = [];

        if ($this->getType() !== null) {
            $data['type'] = $this->getType();
        }

        if ($this->getNullable()) {
            $data['nullable'] = true;
        }

        if ($this->getItems() !== []) {
            $data['items'] = $this->getItems();
        } else {
            if ($this->getType() === TypeEnum::ARRAY->value) {
                $data['items'] = new \stdClass();
            }
        }

        return $data;
    }

    /**
     * @return mixed[]
     */
    public function getSchema(): array
    {
        return [
            'type' => $this->getType(),
            'nullable' => $this->getNullable(),
            'items' => $this->getItems(),
        ];
    }
}
