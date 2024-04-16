<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('valantic.pimcore_api_doc.controller')]
abstract class ApiController extends FrontendController {}
