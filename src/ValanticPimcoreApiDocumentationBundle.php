<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\PimcoreBundleAdminClassicInterface;
use Pimcore\Extension\Bundle\Traits\BundleAdminClassicTrait;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Valantic\PimcoreApiDocumentationBundle\DependencyInjection\CompilerPass\DocsGeneratorCompilerPass;

class ValanticPimcoreApiDocumentationBundle extends AbstractPimcoreBundle implements PimcoreBundleAdminClassicInterface
{
    use BundleAdminClassicTrait;
    use PackageVersionTrait;

    public function getJsPaths(): array
    {
        return [
            '/bundles/valanticpimcoreapidocumentation/js/pimcore/startup.js',
        ];
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new DocsGeneratorCompilerPass());
    }

    protected function getComposerPackageName(): string
    {
        return 'valantic/pimcore-api-documentation-bundle';
    }
}
