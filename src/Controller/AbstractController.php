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
     * Renders a template with the requested HTTP response metadata.
     *
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

    /**
     * Derives the template directory name from the controller class name.
     */
    final protected function getControllerTemplatePathName():string
    {
        return strtolower(str_replace('Controller', '', $this->getControllerName()));
    }

    /**
     * Renders a template resolved from the current controller and action names.
     *
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
