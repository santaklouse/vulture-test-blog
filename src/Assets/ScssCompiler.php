<?php

declare(strict_types=1);

namespace App\Assets;

use RuntimeException;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

final class ScssCompiler
{
    /**
     * Compiles the SCSS to CSS
     */
    public function compile(string $inputFile, string $outputFile): void
    {
        if (!is_file($inputFile)) {
            throw new RuntimeException(sprintf('SCSS entry file not found: %s', $inputFile));
        }

        $outputDirectory = dirname($outputFile);

        if (!is_dir($outputDirectory)
            && !mkdir($outputDirectory, 0775, true)
            && !is_dir($outputDirectory)
        ) {
            throw new RuntimeException(sprintf('Unable to create CSS output directory: %s', $outputDirectory));
        }

        $compiler = new Compiler();
        $compiler->setOutputStyle(OutputStyle::EXPANDED);

        $css = "/* This file is generated from assets/scss/app.scss. */\n"
            . $compiler->compileFile($inputFile)->getCss();
        $temporaryFile = $outputFile . '.tmp';

        if (file_put_contents($temporaryFile, $css, LOCK_EX) === false) {
            throw new RuntimeException(sprintf('Unable to write compiled CSS: %s', $temporaryFile));
        }

        if (!rename($temporaryFile, $outputFile)) {
            throw new RuntimeException(sprintf('Unable to replace compiled CSS: %s', $outputFile));
        }
    }
}
