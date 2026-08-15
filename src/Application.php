<?php

declare(strict_types=1);

namespace App;

use App\Container\Container;
use App\Controller\ErrorController;
use App\Database\ConnectionFactory;
use App\Database\DatabaseConfig;
use App\Http\Request;
use App\Http\Response;
use App\Routing\Exception\MethodNotAllowedException;
use App\Routing\Exception\RouteNotFoundException;
use App\Routing\Router;
use App\View\SmartyView;
use PDO;
use RuntimeException;
use Throwable;

final class Application
{
    private readonly Router $router;

    private readonly ErrorController $errorController;

    public function __construct(private readonly string $projectRoot)
    {
        $container = new Container();
        $this->registerServices($container);

        $this->errorController = $container->get(ErrorController::class);
        $this->router = new Router($container);
        $this->loadRoutes();
    }

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

    private function loadRoutes(): void
    {
        $routesFile = $this->projectRoot . '/config/routes.php';

        if (!is_file($routesFile)) {
            throw new RuntimeException(sprintf('Routes file not found: %s', $routesFile));
        }

        $registerRoutes = require $routesFile;

        if (!is_callable($registerRoutes)) {
            throw new RuntimeException(sprintf('Routes file must return a callable: %s', $routesFile));
        }

        $registerRoutes($this->router);
    }

    private function registerServices(Container $container): void
    {
        $connection = (new ConnectionFactory(
            DatabaseConfig::fromEnvironment(),
        ))->create();

        $container->set(SmartyView::class, new SmartyView($this->projectRoot));
        $container->set(PDO::class, $connection);
    }
}
