<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Valantic\PimcoreApiDocumentationBundle\Http\Request\ApiRequest;

#[AutoconfigureTag('valantic.pimcore_api_doc.controller')]
abstract class ApiController extends FrontendController
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
