<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

final class ConnectionFactory
{
    public function __construct(private readonly DatabaseConfig $config)
    {
    }

    /**
     * Creates a PDO connection
     */
    public function create(): PDO
    {
        return new PDO(
            $this->config->getDsn(),
            $this->config->getUsername(),
            $this->config->getPassword(),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ],
        );
    }
}
