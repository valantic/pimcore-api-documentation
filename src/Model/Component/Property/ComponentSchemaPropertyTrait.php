<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model\Component\Property;

use Valantic\PimcoreApiDocumentationBundle\Model\Component\ComponentSchemaDoc;

trait ComponentSchemaPropertyTrait
{
    /** @var ComponentSchemaDoc[] */
    private array $schemas = [];

    /**
     * @return ComponentSchemaDoc[]
     */
    public function getSchemas(): array
    {
        return $this->schemas;
    }

    /**
     * @param ComponentSchemaDoc[] $schema
     */
    public function setSchemas(array $schema): void
    {
        $this->schemas = $schema;
    }
}
