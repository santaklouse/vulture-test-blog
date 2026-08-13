<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use Smarty\Smarty;

final class HomeController extends AbstractController
{
    public function index(Request $request): Response
    {

        return $this->renderControllerView('index', [
            'pageTitle' => 'Vulture Blog',
            'currentPath' => $request->getPath(),
            'phpVersion' => PHP_VERSION,
            'smartyVersion' => Smarty::SMARTY_VERSION,
        ]);
    }
}

