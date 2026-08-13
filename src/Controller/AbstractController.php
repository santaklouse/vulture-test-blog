<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Response;
use App\View\SmartyView;

abstract class AbstractController
{
    public function __construct(protected readonly SmartyView $view)
    {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    final protected function render(
        string $template,
        array $data = [],
        int $statusCode = 200,
        array $headers = [],
    ): Response {
        return $this->view->render($template, $data, $statusCode, $headers);
    }

    final protected function getControllerName():string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    final protected function getControllerTemplatePathName():string
    {
        return strtolower(str_replace('Controller', '', $this->getControllerName()));
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    final protected function renderControllerView(
        string $controllerActionName,
        array  $data = [],
        int    $statusCode = 200,
        array  $headers = [],
    ): Response {
        $templatePath = implode(DIRECTORY_SEPARATOR, [
            'pages',
            $this->getControllerTemplatePathName(),
            $controllerActionName
        ]);
        return $this->render($templatePath, $data, $statusCode, $headers);
    }
}

