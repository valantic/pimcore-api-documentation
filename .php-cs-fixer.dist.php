<?php

require_once __DIR__ . '/vendor/autoload.php';

use Valantic\PhpCsFixerConfig\ConfigFactory;

return ConfigFactory::createValanticConfig()
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/src')
    )
    ->setRiskyAllowed(true);

