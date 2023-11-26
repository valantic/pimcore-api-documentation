<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model\Component\Property;

class StringPropertyDoc extends AbstractPropertyDoc
{
    protected ?string $format = null;

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): StringPropertyDoc
    {
        $this->format = $format;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        if ($this->getFormat() !== null) {
            $data['format'] = $this->getFormat();
        }

        return $data;
    }
}
