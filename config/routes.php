<?php

declare(strict_types=1);

use App\Controller\CategoryController;
use App\Controller\HomeController;
use App\Controller\PostController;
use App\Routing\Router;

return static function (
    Router $router,
    HomeController $homeController,
    CategoryController $categoryController,
    PostController $postController,
): void {
    $router->get('/', [$homeController, 'index']);
    $router->get('/categories/(?P<slug>[a-z0-9-]+)', [$categoryController, 'show']);
    $router->get('/posts/(?P<slug>[a-z0-9-]+)', [$postController, 'show']);
};
