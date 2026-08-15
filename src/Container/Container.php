<?php

declare(strict_types=1);

namespace App\Container;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

final class Container
{
    /** @var array<string, object> */
    private array $services = [];

    /** @var array<string, true> */
    private array $resolving = [];

    public function set(string $id, object $service): void
    {
        $this->services[$id] = $service;
    }

    public function get(string $id): object
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (isset($this->resolving[$id])) {
            throw new RuntimeException(sprintf('Circular dependency detected: %s', $id));
        }

        $this->resolving[$id] = true;

        try {
            $service = $this->autowire($id);
            return $this->services[$id] = $service;
        } finally {
            unset($this->resolving[$id]);
        }
    }

    /** Creates a class and resolves its constructor dependencies. */
    private function autowire(string $id): object
    {
        if (!class_exists($id)) {
            throw new RuntimeException(sprintf('Service is not registered: %s', $id));
        }

        $reflection = new ReflectionClass($id);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException(sprintf('Service is not instantiable: %s', $id));
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $arguments[] = $this->get($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }

            throw new RuntimeException(sprintf(
                'Cannot resolve parameter $%s of %s.',
                $parameter->getName(),
                $id,
            ));
        }

        return $reflection->newInstanceArgs($arguments);
    }
}
