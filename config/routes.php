<?php

declare(strict_types=1);

use App\Controller\CategoryController;
use App\Controller\HomeController;
use App\Controller\PostController;
use App\Routing\Router;

return static function (Router $router): void {
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/categories/(?P<slug>[a-z0-9-]+)', [CategoryController::class, 'show']);
    $router->get('/posts/(?P<slug>[a-z0-9-]+)', [PostController::class, 'show']);
};
