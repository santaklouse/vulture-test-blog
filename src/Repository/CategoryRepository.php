<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Category;
use PDO;

final class CategoryRepository
{
    public function __construct(private readonly PDO $connection)
    {
    }

    /**
     * Saves a category
     */
    public function save(Category $category): int
    {
        $statement = $this->connection->prepare(<<<'SQL'
            INSERT INTO categories (name, slug, description)
            VALUES (:name, :slug, :description)
            ON DUPLICATE KEY UPDATE
                id = LAST_INSERT_ID(id),
                name = VALUES(name),
                description = VALUES(description)
            SQL);
        $statement->execute([
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    /**
     * Finds a category by slug
     */
    public function findBySlug(string $slug): ?Category
    {
        $statement = $this->connection->prepare(<<<'SQL'
            SELECT id, name, slug, description
            FROM categories
            WHERE slug = :slug
            LIMIT 1
            SQL);
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    /**
     * Returns all categories.
     */
    public function findAll(): array
    {
        $rows = $this->connection
            ->query('SELECT id, name, slug, description FROM categories ORDER BY name')
            ->fetchAll();

        return array_map($this->hydrate(...), $rows);
    }

    /** Returns categories that contain published posts. */
    public function findWithPublishedPosts(): array
    {
        $rows = $this->connection->query(<<<'SQL'
            SELECT c.id, c.name, c.slug, c.description
            FROM categories c
            WHERE EXISTS (
                SELECT 1
                FROM post_categories pc
                INNER JOIN posts p ON p.id = pc.post_id
                WHERE pc.category_id = c.id
                  AND p.published_at <= CURRENT_TIMESTAMP
            )
            ORDER BY c.name
            SQL)->fetchAll();

        return array_map($this->hydrate(...), $rows);
    }

    /** Returns categories assigned to a post. */
    public function findByPostId(int $postId): array
    {
        $statement = $this->connection->prepare(<<<'SQL'
            SELECT c.id, c.name, c.slug, c.description
            FROM categories c
            INNER JOIN post_categories pc ON pc.category_id = c.id
            WHERE pc.post_id = :post_id
            ORDER BY c.name
            SQL);
        $statement->execute(['post_id' => $postId]);

        return array_map($this->hydrate(...), $statement->fetchAll());
    }

    /** Creates a category model. */
    private function hydrate(array $row): Category
    {
        return new Category(
            (int) $row['id'],
            (string) $row['name'],
            (string) $row['slug'],
            $row['description'] === null ? null : (string) $row['description'],
        );
    }
}
