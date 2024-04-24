<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Command;

use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DocsGeneratorInterface;

class DocGeneratorCommand extends AbstractCommand
{
    public function __construct(
        private readonly DocsGeneratorInterface $docsGenerator,
        #[Autowire('%valantic.pimcore_api_doc.docs_file%')]
        private readonly string $filePath,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('valantic:api-doc:generate')
            ->setDescription('Generate api docs for controller actions.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->docsGenerator->generate($this->filePath);

        return self::SUCCESS;
    }
}
