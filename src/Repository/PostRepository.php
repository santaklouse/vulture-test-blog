<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Post;
use DateTimeImmutable;
use Exception;
use PDO;
use RuntimeException;

final class PostRepository
{
    public function __construct(private readonly PDO $connection)
    {
    }

    /**
     * Creates a post.
     *
     * @param Post $post
     * @return int
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
     * @throws Exception
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
     * Finds a published post by slug.
     *
     * @param string $slug
     * @return ?Post
     * @throws Exception
     */
    public function findPublishedBySlug(string $slug): ?Post
    {
        $statement = $this->connection->prepare(<<<'SQL'
            SELECT id, image, title, slug, description, body, views_count, published_at
            FROM posts
            WHERE slug = :slug
              AND published_at <= CURRENT_TIMESTAMP
            LIMIT 1
            SQL);
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    /**
     * Counts published posts in a category.
     *
     * @param int $categoryId
     * @return int
     */
    public function countByCategory(int $categoryId): int
    {
        $statement = $this->connection->prepare(<<<'SQL'
            SELECT COUNT(*)
            FROM posts p
            INNER JOIN post_categories pc ON pc.post_id = p.id
            WHERE pc.category_id = :category_id
              AND p.published_at <= CURRENT_TIMESTAMP
            SQL);
        $statement->execute(['category_id' => $categoryId]);

        return (int) $statement->fetchColumn();
    }

    /**
     * Returns a page of category posts.
     *
     * @param int $categoryId
     * @param string $sort
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findByCategory(
        int $categoryId,
        string $sort,
        int $limit,
        int $offset,
    ): array {
        if ($limit < 1 || $offset < 0) {
            throw new RuntimeException('Invalid pagination values.');
        }

        $orderBy = match ($sort) {
            'views' => 'p.views_count DESC, p.id DESC',
            default => 'p.published_at DESC, p.id DESC',
        };
        $statement = $this->connection->prepare(sprintf(<<<'SQL'
            SELECT p.id, p.image, p.title, p.slug, p.description, p.body,
                   p.views_count, p.published_at
            FROM posts p
            INNER JOIN post_categories pc ON pc.post_id = p.id
            WHERE pc.category_id = :category_id
              AND p.published_at <= CURRENT_TIMESTAMP
            ORDER BY %s
            LIMIT :limit OFFSET :offset
            SQL, $orderBy));
        $statement->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return array_map($this->hydrate(...), $statement->fetchAll());
    }

    /**
     * Increments and returns the view count.
     *
     * @param int $postId
     * @return int
     */
    public function incrementViews(int $postId): int
    {
        $update = $this->connection->prepare(<<<'SQL'
            UPDATE posts
            SET views_count = views_count + 1
            WHERE id = :post_id
            SQL);
        $update->execute(['post_id' => $postId]);

        if ($update->rowCount() !== 1) {
            throw new RuntimeException('Post not found while updating views.');
        }

        $select = $this->connection->prepare(
            'SELECT views_count FROM posts WHERE id = :post_id',
        );
        $select->execute(['post_id' => $postId]);

        return (int) $select->fetchColumn();
    }

    /**
     * Returns related published posts.
     *
     * @param int $postId
     * @param int $limit
     * @return array
     */
    public function findRelated(int $postId, int $limit = 3): array
    {
        if ($limit < 1) {
            throw new RuntimeException('Post limit must be greater than zero.');
        }

        $statement = $this->connection->prepare(<<<'SQL'
            SELECT
                p.id,
                p.image,
                p.title,
                p.slug,
                p.description,
                p.body,
                p.views_count,
                p.published_at,
                COUNT(DISTINCT related_categories.category_id) AS shared_categories
            FROM posts p
            INNER JOIN post_categories related_categories ON related_categories.post_id = p.id
            WHERE related_categories.category_id IN (
                SELECT current_categories.category_id
                FROM post_categories current_categories
                WHERE current_categories.post_id = :current_post_id
            )
              AND p.id <> :excluded_post_id
              AND p.published_at <= CURRENT_TIMESTAMP
            GROUP BY
                p.id,
                p.image,
                p.title,
                p.slug,
                p.description,
                p.body,
                p.views_count,
                p.published_at
            ORDER BY shared_categories DESC, p.published_at DESC, p.id DESC
            LIMIT :limit
            SQL);
        $statement->bindValue(':current_post_id', $postId, PDO::PARAM_INT);
        $statement->bindValue(':excluded_post_id', $postId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return array_map($this->hydrate(...), $statement->fetchAll());
    }

    /**
     * Returns recent posts grouped by category.
     *
     * @param array $categoryIds
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public function findLatestByCategoryIds(array $categoryIds, int $limit = 3): array
    {
        if ($categoryIds === []) {
            return [];
        }

        if ($limit < 1) {
            throw new RuntimeException('Post limit must be greater than zero.');
        }

        $placeholders = implode(', ', array_fill(0, count($categoryIds), '?'));
        $statement = $this->connection->prepare(sprintf(<<<'SQL'
            SELECT
                id,
                image,
                title,
                slug,
                description,
                body,
                views_count,
                published_at,
                category_id
            FROM (
                SELECT
                    p.id,
                    p.image,
                    p.title,
                    p.slug,
                    p.description,
                    p.body,
                    p.views_count,
                    p.published_at,
                    pc.category_id,
                    ROW_NUMBER() OVER (
                        PARTITION BY pc.category_id
                        ORDER BY p.published_at DESC, p.id DESC
                    ) AS position
                FROM posts p
                INNER JOIN post_categories pc ON pc.post_id = p.id
                WHERE pc.category_id IN (%s)
                  AND p.published_at <= CURRENT_TIMESTAMP
            ) ranked_posts
            WHERE position <= ?
            ORDER BY category_id, published_at DESC, id DESC
            SQL, $placeholders));

        $parameter = 1;

        foreach ($categoryIds as $categoryId) {
            $statement->bindValue($parameter++, $categoryId, PDO::PARAM_INT);
        }

        $statement->bindValue($parameter, $limit, PDO::PARAM_INT);
        $statement->execute();
        $postsByCategory = [];

        while (($row = $statement->fetch()) !== false) {
            $categoryId = (int) $row['category_id'];
            $postsByCategory[$categoryId][] = $this->hydrate($row);
        }

        return $postsByCategory;
    }

    /**
     * Updates post categories.
     *
     * @param int $postId
     * @param array $categoryIds
     * @return void
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
     * @throws Exception
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
