<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model\Doc;

class ControllerDoc
{
    /** @var MethodDoc[] */
    private array $methodsDocs = [];

    /**
     * @return MethodDoc[]
     */
    public function getMethodsDocs(): array
    {
        return $this->methodsDocs;
    }

    public function addMethodDoc(MethodDoc $methodDoc): self
    {
        $this->methodsDocs[$methodDoc->getName()] = $methodDoc;

        return $this;
    }
}
