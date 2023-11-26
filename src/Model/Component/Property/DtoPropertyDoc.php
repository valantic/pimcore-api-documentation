<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model\Component\Property;

use Valantic\PimcoreApiDocumentationBundle\Contract\Model\Component\Property\ComponentSchemaPropertyInterface;

class DtoPropertyDoc extends AbstractPropertyDoc implements ComponentSchemaPropertyInterface
{
    use ComponentSchemaPropertyTrait;
    private ?string $ref = null;

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(?string $ref): DtoPropertyDoc
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

        if ($this->getNullable() === true) {
            $data['nullable'] = true;
        }

        if ($this->getRef() !== null) {
            $data['$ref'] = $this->getRef();
        }

        return $data;
    }
}
