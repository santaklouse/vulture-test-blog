<?php

declare(strict_types=1);

use App\Database\ConnectionFactory;
use App\Database\DatabaseConfig;
use App\Database\DatabaseSeeder;

require dirname(__DIR__) . '/vendor/autoload.php';

try {
    $data = require dirname(__DIR__) . '/database/seed.php';

    if (!is_array($data)) {
        throw new RuntimeException('The seed file must return an array.');
    }

    $connection = (new ConnectionFactory(DatabaseConfig::fromEnvironment()))->create();
    $result = (new DatabaseSeeder($connection))->seed($data);

    fwrite(STDOUT, sprintf(
        "Seeded %d categories, %d posts, and %d relations.\n",
        $result['categories'],
        $result['posts'],
        $result['relations'],
    ));
} catch (Throwable $exception) {
    fwrite(STDERR, sprintf("Seeding failed: %s\n", $exception->getMessage()));
    exit(1);
}
