<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends FrontendController
{
    /**
     * @Route("/valantic_pimcore_api_documentation")
     */
    public function indexAction(Request $request): Response
    {
        return new Response('Hello world from valantic_pimcore_api_documentation');
    }
}
