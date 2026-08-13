<?php

declare(strict_types=1);

namespace App\View;

use App\Http\Response;
use RuntimeException;
use Smarty\Smarty;

final class SmartyView
{
    private readonly Smarty $smarty;

    public function __construct(string $projectRoot)
    {
        $compileDirectory = $projectRoot . '/runtime/compile';
        $cacheDirectory = $projectRoot . '/runtime/cache';

        $this->ensureDirectoryExists($compileDirectory);
        $this->ensureDirectoryExists($cacheDirectory);

        $this->smarty = new Smarty();
        $this->smarty->setTemplateDir($projectRoot . '/templates');
        $this->smarty->setCompileDir($compileDirectory);
        $this->smarty->setCacheDir($cacheDirectory);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public function render(
        string $template,
        array $data = [],
        int $statusCode = 200,
        array $headers = [],
    ): Response {
        if (!str_contains($template, '.tpl')) {
            $template .= '.tpl';
        }
        $headers = ['Content-Type' => 'text/html; charset=UTF-8'] + $headers;
        $smartyTemplate = $this->smarty->createTemplate($template);
        $smartyTemplate->assign($data);

        return new Response(
            $smartyTemplate->fetch(),
            $statusCode,
            $headers,
        );
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Unable to create runtime directory: %s', $directory));
        }
    }
}
