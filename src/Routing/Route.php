<?php

declare(strict_types=1);

namespace App\Routing;

use App\Http\Request;
use App\Http\Response;
use Closure;
use InvalidArgumentException;
use LogicException;

final class Route
{
    private readonly string $method;

    private readonly string $compiledPattern;

    private readonly Closure $handler;

    public function __construct(string $method, string $pattern, callable $handler)
    {
        $this->method = strtoupper($method);
        $this->compiledPattern = $this->compilePattern($pattern);
        $this->handler = Closure::fromCallable($handler);
    }

    public function supportsMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Extracts named parameters when the request path matches this route.
     *
     * @param string $path
     * @return array<string, string>|null
     */
    public function match(string $path): ?array
    {
        $result = preg_match($this->compiledPattern, $path, $matches, PREG_UNMATCHED_AS_NULL);

        if ($result !== 1) {
            return null;
        }

        return array_filter($matches, function ($value, $name) {
            return is_string($name) && is_string($value);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Invokes the route handler with the request and extracted parameters
     *
     * @param Request $request
     * @param array<string, string> $parameters
     * @return Response
     */
    public function dispatch(Request $request, array $parameters): Response
    {
        $arguments = [$request];

        foreach ($parameters as $name => $value) {
            $arguments[$name] = $value;
        }

        $response = ($this->handler)(...$arguments);

        if (!$response instanceof Response) {
            throw new LogicException('Route handlers must return an instance of App\\Http\\Response.');
        }

        return $response;
    }

    /**
     * Validates route pattern
     */
    private function compilePattern(string $pattern): string
    {
        if ($pattern === '' || !str_starts_with($pattern, '/')) {
            throw new InvalidArgumentException('Route patterns must start with a forward slash.');
        }

        if (str_contains($pattern, '~')) {
            throw new InvalidArgumentException('Route patterns cannot contain the "~" delimiter.');
        }

        $normalizedPattern = $pattern === '/' ? '/' : rtrim($pattern, '/') . '/?';
        $compiledPattern = sprintf('~^%s$~D', $normalizedPattern);

        if (@preg_match($compiledPattern, '/') === false) {
            throw new InvalidArgumentException(sprintf('Invalid route regex: %s', $pattern));
        }

        return $compiledPattern;
    }
}
