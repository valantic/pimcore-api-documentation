<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model\Doc\Request;

use Valantic\PimcoreApiDocumentationBundle\Model\Component\ComponentSchemaDoc;

class RequestDoc
{
    /** @var ComponentSchemaDoc[] */
    private array $componentSchemaDocs = [];

    /** @var ParameterDoc[] */
    private array $parameters = [];

    /** @var mixed[] */
    private array $requestBody = [];

    /**
     * @return ComponentSchemaDoc[]
     */
    public function getComponentSchemaDocs(): array
    {
        return $this->componentSchemaDocs;
    }

    public function addComponentSchemaDoc(?ComponentSchemaDoc $componentSchemaDoc): self
    {
        if ($componentSchemaDoc !== null) {
            $this->componentSchemaDocs[$componentSchemaDoc->getName()] = $componentSchemaDoc;
        }

        return $this;
    }

    /**
     * @return ParameterDoc[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param ParameterDoc[] $parameters
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getRequestBody(): array
    {
        return $this->requestBody;
    }

    /**
     * @param mixed[] $requestBody
     */
    public function setRequestBody(array $requestBody): self
    {
        $this->requestBody = $requestBody;

        return $this;
    }
}
