<?php

declare(strict_types=1);

namespace App\Routing;

use App\Container\Container;
use App\Http\Request;
use App\Http\Response;
use App\Routing\Exception\MethodNotAllowedException;
use App\Routing\Exception\RouteNotFoundException;
use InvalidArgumentException;

final class Router
{
    /** @var list<Route> */
    private array $routes = [];

    private readonly Container $container;

    public function __construct(?Container $container = null)
    {
        $this->container = $container ?? new Container();
    }

    public function get(string $pattern, callable|array $handler): self
    {
        return $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable|array $handler): self
    {
        return $this->add('POST', $pattern, $handler);
    }

    /**
     * Registers a route for an HTTP method and regular-expression path pattern
     *
     * @param string $method
     * @param string $pattern
     * @param callable|array $handler
     * @return self
     */
    public function add(string $method, string $pattern, callable|array $handler): self
    {
        $this->routes[] = new Route($method, $pattern, $this->resolveHandler($handler));

        return $this;
    }

    /**
     * Finds and executes the first route matching the request path and method
     *
     * @param Request $request
     * @return Response
     */
    public function dispatch(Request $request): Response
    {
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            $parameters = $route->match($request->getPath());

            if ($parameters === null) {
                continue;
            }

            if (!$route->supportsMethod($request->getMethod())) {
                $allowedMethods[] = $route->getMethod();
                continue;
            }

            return $route->dispatch($request, $parameters);
        }

        if ($allowedMethods !== []) {
            $allowedMethods = array_values(array_unique($allowedMethods));
            sort($allowedMethods);

            throw new MethodNotAllowedException($allowedMethods);
        }

        throw new RouteNotFoundException(sprintf('No route matches "%s".', $request->getPath()));
    }

    /** Resolves a route handler. */
    private function resolveHandler(callable|array $handler): callable
    {
        if (is_callable($handler)) {
            return $handler;
        }

        if (count($handler) !== 2 || !is_string($handler[0]) || !is_string($handler[1])) {
            throw new InvalidArgumentException('Route handler must contain a class and method name.');
        }

        $resolvedHandler = [$this->container->get($handler[0]), $handler[1]];

        if (!is_callable($resolvedHandler)) {
            throw new InvalidArgumentException(sprintf(
                'Route handler is not callable: %s::%s',
                $handler[0],
                $handler[1],
            ));
        }

        return $resolvedHandler;
    }
}
