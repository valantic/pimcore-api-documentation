<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Controller;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('valantic.pimcore_api_doc.controller')]
interface ApiControllerInterface {}
