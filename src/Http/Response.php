<?php

declare(strict_types=1);

namespace App\Http;

use InvalidArgumentException;

final class Response
{
    /**
     * Creates an HTTP response with a validated status code.
     *
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly string $body = '',
        private readonly int $statusCode = 200,
        private readonly array $headers = [],
    ) {
        if ($this->statusCode < 100 || $this->statusCode > 599) {
            throw new InvalidArgumentException(sprintf('Invalid HTTP status code: %d', $this->statusCode));
        }
    }

    /**
     * Sends the status, headers, and response body to the client.
     */
    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value), true);
        }

        echo $this->body;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
