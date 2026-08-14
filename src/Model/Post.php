<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;

final class Post
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $image,
        public readonly string $title,
        public readonly string $slug,
        public readonly string $description,
        public readonly string $body,
        public readonly int $viewsCount,
        public readonly DateTimeImmutable $publishedAt,
    ) {
    }
}
