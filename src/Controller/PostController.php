<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Model\Category;
use App\Model\Post;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Routing\Exception\RouteNotFoundException;
use App\View\SmartyView;

final class PostController extends AbstractController
{
    public function __construct(
        SmartyView $view,
        private readonly CategoryRepository $categoryRepository,
        private readonly PostRepository $postRepository,
    ) {
        parent::__construct($view);
    }

    public function show(Request $_request, string $slug): Response
    {
        $post = $this->postRepository->findPublishedBySlug($slug);

        if ($post === null || $post->id === null) {
            throw new RouteNotFoundException(sprintf('Post not found: %s', $slug));
        }

        $postData = $post->toArray();
        $postData['viewsCount'] = $this->postRepository->incrementViews($post->id);
        $postData['bodyParagraphs'] = preg_split(
            '/\R{2,}/',
            trim($post->body),
            flags: PREG_SPLIT_NO_EMPTY,
        ) ?: [];
        $categories = $this->categoryRepository->findByPostId($post->id);
        $categoryLinks = array_map(
            static fn (Category $category): array => [
                'name' => $category->name,
                'slug' => $category->slug,
            ],
            $categories,
        );
        $relatedPosts = $this->postRepository->findRelated($post->id);

        return $this->render('pages/post/show', [
            'pageTitle' => sprintf('%s | Vulture Blog', $post->title),
            'post' => $postData,
            'categories' => $categoryLinks,
            'breadcrumbCategory' => $categoryLinks[0] ?? null,
            'relatedPosts' => array_map(
                static fn (Post $post): array => $post->toArray(),
                $relatedPosts,
            ),
        ]);
    }
}
