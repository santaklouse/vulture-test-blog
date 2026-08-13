<?php

declare(strict_types=1);

namespace App;

use RuntimeException;
use Smarty\Exception;
use Smarty\Smarty;

final class Application
{
    public function __construct(
        private readonly string $projectRoot,
    ) {
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        $smarty = $this->createSmarty();


        $smarty->assign([
            'pageTitle' => 'Vulture Blog',
            'phpVersion' => PHP_VERSION,
            'smartyVersion' => Smarty::SMARTY_VERSION,
        ]);

        $smarty->display('home.tpl');
    }

    private function createSmarty(): Smarty
    {
        $compileDirectory = $this->projectRoot . '/runtime/compile';
        $cacheDirectory = $this->projectRoot . '/runtime/cache';

        $this->ensureDirectoryExists($compileDirectory);
        $this->ensureDirectoryExists($cacheDirectory);

        $smarty = new Smarty();
        $smarty->setTemplateDir($this->projectRoot . '/templates');
        $smarty->setCompileDir($compileDirectory);
        $smarty->setCacheDir($cacheDirectory);

        return $smarty;
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Unable to create runtime directory: %s', $directory));
        }
    }
}

