<?php

declare(strict_types=1);

use App\Controller\HomeController;
use App\Routing\Router;

return static function (Router $router, HomeController $homeController): void {
    $router->get('/', [$homeController, 'index']);
};

