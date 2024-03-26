<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Controller;

use Valantic\PimcoreApiDocumentationBundle\Http\Request\ApiRequest;

trait ApiControllerTrait
{
    /**
     * @return array<string, array<int, array<int, string|\Stringable>>>
     */
    public function validateRequest(ApiRequest $request): array
    {
        $errorsData = [];
        $errors = $request->validate();

        foreach ($errors as $error) {
            $errorsData[trim($error->getPropertyPath(), '[]')][] = [
                $error->getMessage(),
            ];
        }

        return $errorsData;
    }
}
