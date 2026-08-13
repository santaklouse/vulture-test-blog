<?php

declare(strict_types=1);

use App\Http\Request;
use App\Http\Response;
use App\Routing\Exception\MethodNotAllowedException;
use App\Routing\Exception\RouteNotFoundException;
use App\Routing\Router;

return [
    'dispatches a static route' => static function (): void {
        $router = new Router();
        $router->get('/', static fn (Request $request): Response => new Response($request->getPath()));

        $response = $router->dispatch(new Request('GET', '/'));

        assertSame('/', $response->getBody());
        assertSame(200, $response->getStatusCode());
    },
    'extracts named regex parameters' => static function (): void {
        $router = new Router();
        $router->get(
            '/posts/(?P<slug>[a-z0-9-]+)',
            static fn (Request $request, string $slug): Response => new Response($slug),
        );

        $response = $router->dispatch(new Request('GET', '/posts/regex-routing-in-php'));

        assertSame('regex-routing-in-php', $response->getBody());
    },
    'accepts one trailing slash' => static function (): void {
        $router = new Router();
        $router->get('/categories', static fn (Request $request): Response => new Response('categories'));

        $response = $router->dispatch(new Request('GET', '/categories/'));

        assertSame('categories', $response->getBody());
    },
    'throws when no route matches the path' => static function (): void {
        $router = new Router();
        $router->get('/', static fn (Request $request): Response => new Response());

        assertThrows(
            static fn (): Response => $router->dispatch(new Request('GET', '/missing')),
            RouteNotFoundException::class,
        );
    },
    'reports methods allowed for a matching path' => static function (): void {
        $router = new Router();
        $router->get('/posts', static fn (Request $request): Response => new Response());
        $router->post('/posts', static fn (Request $request): Response => new Response());

        $exception = assertThrows(
            static fn (): Response => $router->dispatch(new Request('DELETE', '/posts')),
            MethodNotAllowedException::class,
        );

        assertSame(['GET', 'POST'], $exception->getAllowedMethods());
    },
];

