<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Command;

use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DocsGeneratorInterface;

class DocGeneratorCommand extends AbstractCommand
{
    final public const DEFAULT_PATH = PIMCORE_PRIVATE_VAR . '/api-docs/api_documentation.json';
    private const PATH_ARGUMENT = 'path';

    public function __construct(
        private readonly DocsGeneratorInterface $docsGenerator,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('valantic:api-doc:generate')
            ->setDescription('Generate api docs for controller actions.')
            ->addArgument(
                self::PATH_ARGUMENT,
                InputArgument::OPTIONAL,
                'Define path for saving docs.',
                self::DEFAULT_PATH
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument(self::PATH_ARGUMENT);
        $this->docsGenerator->generate($filePath);

        return self::SUCCESS;
    }
}
