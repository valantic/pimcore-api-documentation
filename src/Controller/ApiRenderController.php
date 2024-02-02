<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Controller;

use Nelmio\ApiDocBundle\Render\Html\AssetsMode;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Valantic\PimcoreApiDocumentationBundle\Command\DocGeneratorCommand;

class ApiRenderController extends FrontendController
{
    #[Route('%valantic.pimcore_api_doc.docs_route%')]
    public function renderApi(): Response
    {
        $options = [
            'assets_mode' => AssetsMode::CDN,
            'swagger_ui_config' => [],
        ];

        try {
            $apiDocs = file_get_contents(DocGeneratorCommand::DEFAULT_PATH) ?: throw new \Exception();
        } catch (\Throwable) {
            throw new \Exception('Docs not generated.');
        }

        return $this->render(
            '@NelmioApiDoc/SwaggerUi/index.html.twig',
            [
                'swagger_data' => ['spec' => json_decode($apiDocs, true, flags: \JSON_THROW_ON_ERROR)],
                'assets_mode' => $options['assets_mode'],
                'swagger_ui_config' => $options['swagger_ui_config'],
            ]
        );
    }
}
