<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Contract\Model\Component\Property;

use Valantic\PimcoreApiDocumentationBundle\Model\Component\ComponentSchemaDoc;

interface ComponentSchemaPropertyInterface
{
    /**
     * @return ComponentSchemaDoc[]
     */
    public function getSchemas(): array;

    /**
     * @param ComponentSchemaDoc[] $schema
     */
    public function setSchemas(array $schema): void;
}
