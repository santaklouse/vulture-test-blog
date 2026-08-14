<?php

declare(strict_types=1);

namespace App\Routing\Exception;

use RuntimeException;

final class MethodNotAllowedException extends RuntimeException
{
    /**
     * @param list<string> $allowedMethods
     */
    public function __construct(private readonly array $allowedMethods)
    {
        parent::__construct('The requested HTTP method is not allowed for this route.');
    }

    /**
     * @return list<string>
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}

