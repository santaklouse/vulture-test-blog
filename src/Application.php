<?php

declare(strict_types=1);

namespace App;

use App\Controller\ErrorController;
use App\Controller\CategoryController;
use App\Controller\HomeController;
use App\Controller\PostController;
use App\Database\ConnectionFactory;
use App\Database\DatabaseConfig;
use App\Http\Request;
use App\Http\Response;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Routing\Exception\MethodNotAllowedException;
use App\Routing\Exception\RouteNotFoundException;
use App\Routing\Router;
use App\View\SmartyView;
use RuntimeException;
use Throwable;

final class Application
{
    private readonly Router $router;

    private readonly ErrorController $errorController;

    public function __construct(private readonly string $projectRoot)
    {
        $view = new SmartyView($this->projectRoot);
        $connection = (new ConnectionFactory(
            DatabaseConfig::fromEnvironment(),
        ))->create();
        $categoryRepository = new CategoryRepository($connection);
        $postRepository = new PostRepository($connection);
        $homeController = new HomeController($view, $categoryRepository, $postRepository);
        $categoryController = new CategoryController($view, $categoryRepository, $postRepository);
        $postController = new PostController($view, $categoryRepository, $postRepository);

        $this->errorController = new ErrorController($view);
        $this->router = new Router();
        $this->loadRoutes($homeController, $categoryController, $postController);
    }

    /**
     * Runs the application for the current HTTP request
     */
    public function run(): void
    {
        $this->handle(Request::fromGlobals())->send();
    }

    /**
     * Dispatches a request and converts routing or application errors into responses
     */
    public function handle(Request $request): Response
    {
        try {
            return $this->router->dispatch($request);
        } catch (MethodNotAllowedException $exception) {
            return $this->errorController->methodNotAllowed($request, $exception->getAllowedMethods());
        } catch (RouteNotFoundException) {
            return $this->errorController->notFound($request);
        } catch (Throwable $exception) {
            error_log(sprintf(
                'Unhandled application exception: %s in %s:%d',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
            ));

            return $this->errorController->internalServerError($request);
        }
    }

    /**
     * Loads the central route definitions and registers their controller handlers
     */
    private function loadRoutes(
        HomeController $homeController,
        CategoryController $categoryController,
        PostController $postController,
    ): void {
        $routesFile = $this->projectRoot . '/config/routes.php';

        if (!is_file($routesFile)) {
            throw new RuntimeException(sprintf('Routes file not found: %s', $routesFile));
        }

        $registerRoutes = require $routesFile;

        if (!is_callable($registerRoutes)) {
            throw new RuntimeException(sprintf('Routes file must return a callable: %s', $routesFile));
        }

        $registerRoutes(
            $this->router,
            $homeController,
            $categoryController,
            $postController,
        );
    }
}
