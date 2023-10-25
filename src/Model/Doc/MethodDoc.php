<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model\Doc;

use Valantic\PimcoreApiDocumentationBundle\Model\Component\ComponentSchemaDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Doc\Request\RequestDoc;

class MethodDoc implements \JsonSerializable
{
    private string $name;
    private RouteDoc $routeDoc;
    private ?RequestDoc $requestDoc = null;

    /** @var ResponseDoc[] */
    private array $responsesDoc;

    /** @var ComponentSchemaDoc[] */
    private array $componentSchemas;

    public function getRouteDoc(): RouteDoc
    {
        return $this->routeDoc;
    }

    public function setRouteDoc(RouteDoc $routeDoc): self
    {
        $this->routeDoc = $routeDoc;

        return $this;
    }

    public function getRequestDoc(): ?RequestDoc
    {
        return $this->requestDoc;
    }

    public function setRequestDoc(?RequestDoc $requestDoc): self
    {
        $this->requestDoc = $requestDoc;

        return $this;
    }

    /**
     * @return ResponseDoc[]
     */
    public function getResponsesDoc(): array
    {
        return $this->responsesDoc;
    }

    /**
     * @param ResponseDoc[] $responsesDoc
     */
    public function setResponsesDoc(array $responsesDoc): self
    {
        $this->responsesDoc = $responsesDoc;

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
     * @return ComponentSchemaDoc[]
     */
    public function getComponentSchemas(): array
    {
        return $this->componentSchemas;
    }

    /**
     * @param ComponentSchemaDoc[] $componentSchemas
     */
    public function setComponentSchemas(array $componentSchemas): self
    {
        $this->componentSchemas = $componentSchemas;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        $formattedResponses = [];

        foreach ($this->getResponsesDoc() as $responseDoc) {
            $formattedResponses[$responseDoc->getStatus()] = $responseDoc;
        }

        $data = [
            'parameters' => array_merge($this->getRouteDoc()->getParameters(), $this->getRequestDoc()?->getParameters() ?: []),
            'responses' => $formattedResponses,
        ];

        if ($this->getRequestDoc() !== null && count($this->getRequestDoc()->getRequestBody()) !== 0) {
            $data['requestBody'] = $this->getRequestDoc()->getRequestBody();
        }

        return $data;
    }
}
