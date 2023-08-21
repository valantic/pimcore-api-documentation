<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model\Doc\Request;

class RequestDoc
{
    /** @var ParameterDoc[] */
    private array $parameters = [];

    /** @var mixed[] */
    private array $requestBody = [];

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
