<?php

declare(strict_types=1);

namespace App\Database;

use App\Model\Category;
use App\Model\Post;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use DateTimeImmutable;
use PDO;
use RuntimeException;
use Throwable;

final class DatabaseSeeder
{
    private readonly CategoryRepository $categories;

    private readonly PostRepository $posts;

    public function __construct(private readonly PDO $connection)
    {
        $this->categories = new CategoryRepository($connection);
        $this->posts = new PostRepository($connection);
    }

    /**
     * Seeds the database
     */
    public function seed(array $data): array
    {
        $categoryIds = [];
        $relationCount = 0;

        $this->connection->beginTransaction();

        try {
            foreach ($data['categories'] as $categoryData) {
                $category = new Category(
                    null,
                    (string) $categoryData['name'],
                    (string) $categoryData['slug'],
                    $categoryData['description'] === null
                        ? null
                        : (string) $categoryData['description'],
                );
                $categoryIds[$category->slug] = $this->categories->save($category);
            }

            foreach ($data['posts'] as $postData) {
                $post = new Post(
                    null,
                    $postData['image'] === null ? null : (string) $postData['image'],
                    (string) $postData['title'],
                    (string) $postData['slug'],
                    (string) $postData['description'],
                    (string) $postData['body'],
                    (int) $postData['views_count'],
                    new DateTimeImmutable((string) $postData['published_at']),
                );
                $postId = $this->posts->save($post);
                $postCategoryIds = [];

                foreach ($postData['categories'] as $categorySlug) {
                    if (!isset($categoryIds[$categorySlug])) {
                        throw new RuntimeException(sprintf(
                            'Unknown category slug: %s',
                            $categorySlug,
                        ));
                    }

                    $postCategoryIds[] = $categoryIds[$categorySlug];
                }

                $this->posts->syncCategories($postId, $postCategoryIds);
                $relationCount += count(array_unique($postCategoryIds));
            }

            $this->connection->commit();
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }

        return [
            'categories' => count($data['categories']),
            'posts' => count($data['posts']),
            'relations' => $relationCount,
        ];
    }
}
