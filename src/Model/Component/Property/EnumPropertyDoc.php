<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model\Component\Property;

class EnumPropertyDoc extends AbstractPropertyDoc
{
    /**
     * @var array<int, string>
     */
    protected array $enumOptions = [];

    /**
     * @return array<int, string>
     */
    public function getEnumOptions(): array
    {
        return $this->enumOptions;
    }

    /**
     * @param array<int, string> $enumOptions
     */
    public function setEnumOptions(array $enumOptions): self
    {
        $this->enumOptions = $enumOptions;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data['enum'] = $this->getEnumOptions();

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
            'enum' => $this->getEnumOptions(),
        ];
    }
}
