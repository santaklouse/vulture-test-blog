<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Model\Post;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Routing\Exception\RouteNotFoundException;
use App\View\SmartyView;

final class CategoryController extends AbstractController
{
    private const POSTS_PER_PAGE = 6;

    public function __construct(
        SmartyView $view,
        private readonly CategoryRepository $categoryRepository,
        private readonly PostRepository $postRepository,
    ) {
        parent::__construct($view);
    }

    public function show(Request $request, string $slug): Response
    {
        $category = $this->categoryRepository->findBySlug($slug);

        if ($category === null || $category->id === null) {
            throw new RouteNotFoundException(sprintf('Category not found: %s', $slug));
        }

        $sort = $this->readSort($request);
        $page = $this->readPage($request);
        $totalPosts = $this->postRepository->countByCategory($category->id);
        $totalPages = max(1, (int) ceil($totalPosts / self::POSTS_PER_PAGE));

        if ($page > $totalPages) {
            throw new RouteNotFoundException(sprintf('Category page not found: %d', $page));
        }

        $posts = $this->postRepository->findByCategory(
            $category->id,
            $sort,
            self::POSTS_PER_PAGE,
            ($page - 1) * self::POSTS_PER_PAGE,
        );

        return $this->render('pages/category/show', [
            'pageTitle' => sprintf('%s | Vulture Blog', $category->name),
            'category' => [
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
            ],
            'posts' => array_map(
                static fn (Post $post): array => $post->toArray(),
                $posts,
            ),
            'sort' => $sort,
            'sortLinks' => [
                'date' => $this->buildUrl($category->slug, 'date', 1),
                'views' => $this->buildUrl($category->slug, 'views', 1),
            ],
            'pagination' => $this->buildPagination(
                $category->slug,
                $sort,
                $page,
                $totalPages,
            ),
            'totalPosts' => $totalPosts,
        ]);
    }

    private function readSort(Request $request): string
    {
        $sort = $request->getQuery('sort', 'date');

        return in_array($sort, ['date', 'views'], true)
            ? $sort
            : 'date';
    }

    private function readPage(Request $request): int
    {
        $page = $request->getQuery('page', '1');

        if (!is_string($page) && !is_int($page)) {
            return 1;
        }

        $page = (string) $page;

        return ctype_digit($page) && (int) $page > 0 ? (int) $page : 1;
    }

    /**
     * Builds the values used by the pagination template.
     *
     * @param string $slug
     * @param string $sort
     * @param int $currentPage
     * @param int $totalPages
     * @return array
     */
    private function buildPagination(
        string $slug,
        string $sort,
        int $currentPage,
        int $totalPages,
    ): array {
        $pages = [];

        for ($page = 1; $page <= $totalPages; $page++) {
            $pages[] = [
                'number' => $page,
                'url' => $this->buildUrl($slug, $sort, $page),
                'current' => $page === $currentPage,
            ];
        }

        return [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'previousUrl' => $currentPage > 1
                ? $this->buildUrl($slug, $sort, $currentPage - 1)
                : null,
            'nextUrl' => $currentPage < $totalPages
                ? $this->buildUrl($slug, $sort, $currentPage + 1)
                : null,
            'pages' => $pages,
        ];
    }

    /**
     * @param string $slug
     * @param string $sort
     * @param int $page
     * @return string
     */
    private function buildUrl(string $slug, string $sort, int $page): string
    {
        return sprintf(
            '/categories/%s?%s',
            rawurlencode($slug),
            http_build_query(['sort' => $sort, 'page' => $page]),
        );
    }
}
