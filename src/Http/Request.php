<?php

declare(strict_types=1);

namespace App\Http;

final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $parsedBody
     * @param array<string, mixed> $server
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query = [],
        private readonly array $parsedBody = [],
        private readonly array $server = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($requestUri, PHP_URL_PATH);

        return new self(
            $method,
            is_string($path) && $path !== '' ? rawurldecode($path) : '/',
            $_GET,
            $_POST,
            $_SERVER,
        );
    }

    public function getMethod(): string
    {
        return strtoupper($this->method);
    }

    public function getPath(): string
    {
        if ($this->path === '') {
            return '/';
        }

        return str_starts_with($this->path, '/') ? $this->path : '/' . $this->path;
    }

    public function getQuery(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function getQueryParams(): array
    {
        return $this->query;
    }

    public function getParsedBody(string $key, mixed $default = null): mixed
    {
        return $this->parsedBody[$key] ?? $default;
    }

    public function getServer(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}

