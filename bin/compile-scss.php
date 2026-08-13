<?php

declare(strict_types=1);

use App\Assets\ScssCompiler;

require dirname(__DIR__) . '/vendor/autoload.php';

$projectRoot = dirname(__DIR__);

$inputFile = $projectRoot . '/assets/scss/app.scss';
$outputFile = $projectRoot . '/public/assets/css/app.css';

(new ScssCompiler())->compile($inputFile, $outputFile);

fwrite(STDOUT, sprintf("Compiled %s to %s\n", $inputFile, $outputFile));

