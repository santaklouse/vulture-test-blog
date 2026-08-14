<?php

declare(strict_types=1);

namespace App\Database;

use InvalidArgumentException;
use RuntimeException;

final class DatabaseConfig
{
    private function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $database,
        private readonly string $username,
        private readonly string $password,
    ) {
    }

    /**
     * Builds database connection settings from environment variables.
     */
    public static function fromEnvironment(): self
    {
        $host = self::getEnvVariable('DB_HOST');
        $port = filter_var(
            self::getEnvVariable('DB_PORT'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 65535]],
        );
        $database = self::getEnvVariable('DB_DATABASE');
        $username = self::getEnvVariable('DB_USERNAME');
        $password = getenv('DB_PASSWORD');

        if ($port === false) {
            throw new InvalidArgumentException('DB_PORT must be an integer between 1 and 65535.');
        }

        if (str_contains($host, ';') || str_contains($database, ';')) {
            throw new InvalidArgumentException('DB_HOST and DB_DATABASE must not contain semicolons.');
        }

        if ($password === false) {
            throw new RuntimeException('Required environment variable DB_PASSWORD is not set.');
        }

        return new self($host, $port, $database, $username, $password);
    }

    /**
     * Returns a UTF-8 MySQL DSN for PDO.
     */
    public function getDsn(): string
    {
        return sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $this->host,
            $this->port,
            $this->database,
        );
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Reads a environment variable.
     */
    private static function getEnvVariable(string $name): string
    {
        $value = getenv($name);

        if ($value === false || trim($value) === '') {
            throw new RuntimeException(sprintf('Required environment variable %s is not set.', $name));
        }

        return $value;
    }
}
