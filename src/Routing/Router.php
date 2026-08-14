<?php

declare(strict_types=1);

namespace App\Routing;

use App\Http\Request;
use App\Http\Response;
use App\Routing\Exception\MethodNotAllowedException;
use App\Routing\Exception\RouteNotFoundException;

final class Router
{
    /** @var list<Route> */
    private array $routes = [];

    public function get(string $pattern, callable $handler): self
    {
        return $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): self
    {
        return $this->add('POST', $pattern, $handler);
    }

    /**
     * Registers a route for an HTTP method and regular-expression path pattern
     *
     * @param string $method
     * @param string $pattern
     * @param callable $handler
     * @return self
     */
    public function add(string $method, string $pattern, callable $handler): self
    {
        $this->routes[] = new Route($method, $pattern, $handler);

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
}
