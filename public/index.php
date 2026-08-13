<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

(new App\Application(dirname(__DIR__)))->run();
