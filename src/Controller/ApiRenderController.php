<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Controller;

use Nelmio\ApiDocBundle\Render\Html\AssetsMode;
use Pimcore\Controller\FrontendController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Valantic\PimcoreApiDocumentationBundle\Exception\UnableToDisplayDocumentation;

class ApiRenderController extends FrontendController
{
    #[Route('%valantic.pimcore_api_doc.docs_route%')]
    public function renderApi(
        #[Autowire('%valantic.pimcore_api_doc.docs_file%')]
        string $filePath,
    ): Response {
        $options = [
            'assets_mode' => AssetsMode::CDN,
            'swagger_ui_config' => [],
        ];

        try {
            $apiDocs = file_get_contents($filePath);

            if ($apiDocs === false) {
                throw new UnableToDisplayDocumentation(sprintf('File at %s could not be read', $filePath));
            }
        } catch (\Throwable $throwable) {
            throw new UnableToDisplayDocumentation('See previous exception', previous: $throwable);
        }

        return $this->render(
            '@NelmioApiDoc/SwaggerUi/index.html.twig',
            [
                'swagger_data' => ['spec' => json_decode($apiDocs, true, flags: \JSON_THROW_ON_ERROR)],
                'assets_mode' => $options['assets_mode'],
                'swagger_ui_config' => $options['swagger_ui_config'],
            ],
        );
    }
}
