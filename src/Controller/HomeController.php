<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Model\Category;
use App\Model\Post;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\View\SmartyView;

final class HomeController extends AbstractController
{
    public function __construct(
        SmartyView $view,
        private readonly CategoryRepository $categoryRepository,
        private readonly PostRepository $postRepository,
    ) {
        parent::__construct($view);
    }

    /**
     * @throws \Exception
     */
    public function index(Request $_request): Response
    {
        $categories = $this->categoryRepository->findWithPublishedPosts();
        $categoryIds = array_map(
            static fn (Category $category): int => (int) $category->id,
            $categories,
        );
        $postsByCategory = $this->postRepository->findLatestByCategoryIds($categoryIds);
        $categorySections = [];

        foreach ($categories as $category) {
            $posts = $postsByCategory[$category->id] ?? [];
            $categorySections[] = [
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'posts' => array_map(
                    static fn (Post $post): array => $post->toArray(),
                    $posts,
                ),
            ];
        }

        return $this->render('pages/home/index', [
            'pageTitle' => 'Vulture Blog',
            'categorySections' => $categorySections,
        ]);
    }
}
