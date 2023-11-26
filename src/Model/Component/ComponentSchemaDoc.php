<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model\Component;

use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\AbstractPropertyDoc;

class ComponentSchemaDoc implements \JsonSerializable
{
    final public const TYPE_OBJECT = 'object';
    private string $type;
    private string $name;

    /** @var AbstractPropertyDoc[] */
    private array $properties;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return AbstractPropertyDoc[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param AbstractPropertyDoc[] $properties
     */
    public function setProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        $formattedProperties = [];

        foreach ($this->getProperties() as $property) {
            $formattedProperties[$property->getName()] = $property;
        }

        return [
            'type' => $this->getType(),
            'properties' => $formattedProperties,
        ];
    }
}
