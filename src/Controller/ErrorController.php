<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;

final class ErrorController extends AbstractController
{
    /**
     * Renders a response for an unknown path.
     */
    public function notFound(Request $request): Response
    {
        return $this->render('errors/error', [
            'pageTitle' => 'Page not found',
            'statusCode' => 404,
            'message' => sprintf('No page matches the path "%s".', $request->getPath()),
        ], 404);
    }

    /**
     * Renders a response when the path exists but the HTTP method is unsupported.
     *
     * @param list<string> $allowedMethods
     */
    public function methodNotAllowed(Request $request, array $allowedMethods): Response
    {
        return $this->render('errors/error', [
            'pageTitle' => 'Method not allowed',
            'statusCode' => 405,
            'message' => sprintf(
                'The %s method is not allowed for "%s".',
                $request->getMethod(),
                $request->getPath(),
            ),
        ], 405, [
            'Allow' => implode(', ', $allowedMethods),
        ]);
    }

    /**
     * Renders a generic response for an unexpected application failure.
     */
    public function internalServerError(Request $request): Response
    {
        return $this->render('errors/error', [
            'pageTitle' => 'Internal server error',
            'statusCode' => 500,
            'message' => 'The request could not be completed. Please try again later.',
        ], 500);
    }
}
