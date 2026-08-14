<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Post;
use DateTimeImmutable;
use PDO;
use RuntimeException;

final class PostRepository
{
    public function __construct(private readonly PDO $connection)
    {
    }

    /**
     * Creates a post.
     */
    public function save(Post $post): int
    {
        $statement = $this->connection->prepare(<<<'SQL'
            INSERT INTO posts (
                image,
                title,
                slug,
                description,
                body,
                views_count,
                published_at
            ) VALUES (
                :image,
                :title,
                :slug,
                :description,
                :body,
                :views_count,
                :published_at
            )
            ON DUPLICATE KEY UPDATE
                id = LAST_INSERT_ID(id),
                image = VALUES(image),
                title = VALUES(title),
                description = VALUES(description),
                body = VALUES(body),
                views_count = VALUES(views_count),
                published_at = VALUES(published_at)
            SQL);
        $statement->execute([
            'image' => $post->image,
            'title' => $post->title,
            'slug' => $post->slug,
            'description' => $post->description,
            'body' => $post->body,
            'views_count' => $post->viewsCount,
            'published_at' => $post->publishedAt->format('Y-m-d H:i:s'),
        ]);

        return (int) $this->connection->lastInsertId();
    }

    /**
     * Finds a post by slug.
     */
    public function findBySlug(string $slug): ?Post
    {
        $statement = $this->connection->prepare(<<<'SQL'
            SELECT id, image, title, slug, description, body, views_count, published_at
            FROM posts
            WHERE slug = :slug
            LIMIT 1
            SQL);
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    /**
     * Updates post categories.
     */
    public function syncCategories(int $postId, array $categoryIds): void
    {
        if ($categoryIds === []) {
            throw new RuntimeException('A post must have at least one category.');
        }

        $delete = $this->connection->prepare(
            'DELETE FROM post_categories WHERE post_id = :post_id',
        );
        $delete->execute(['post_id' => $postId]);

        $insert = $this->connection->prepare(<<<'SQL'
            INSERT INTO post_categories (post_id, category_id)
            VALUES (:post_id, :category_id)
            SQL);

        foreach (array_unique($categoryIds) as $categoryId) {
            $insert->execute([
                'post_id' => $postId,
                'category_id' => $categoryId,
            ]);
        }
    }

    /**
     * Creates a post model.
     */
    private function hydrate(array $row): Post
    {
        return new Post(
            (int) $row['id'],
            $row['image'] === null ? null : (string) $row['image'],
            (string) $row['title'],
            (string) $row['slug'],
            (string) $row['description'],
            (string) $row['body'],
            (int) $row['views_count'],
            new DateTimeImmutable((string) $row['published_at']),
        );
    }
}
