<?php

declare(strict_types=1);

use App\Assets\ScssCompiler;

require dirname(__DIR__) . '/vendor/autoload.php';

$projectRoot = dirname(__DIR__);
$sourceDirectory = $projectRoot . '/assets/scss';
$inputFile = $sourceDirectory . '/app.scss';
$outputFile = $projectRoot . '/public/assets/css/app.css';
$compiler = new ScssCompiler();

$getLatestModificationTime = static function () use ($sourceDirectory): int {
    $latestModificationTime = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDirectory, RecursiveDirectoryIterator::SKIP_DOTS),
    );

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'scss') {
            $latestModificationTime = max($latestModificationTime, $file->getMTime());
        }
    }

    return $latestModificationTime;
};

$lastCompilationTime = -1;
fwrite(STDOUT, sprintf("Watching %s for SCSS changes. Press Ctrl+C to stop.\n", $sourceDirectory));

while (true) {
    $latestModificationTime = $getLatestModificationTime();

    if ($latestModificationTime > $lastCompilationTime) {
        $lastCompilationTime = $latestModificationTime;

        try {
            $compiler->compile($inputFile, $outputFile);
            fwrite(STDOUT, sprintf("[%s] CSS rebuilt successfully.\n", date('H:i:s')));
        } catch (Throwable $exception) {
            fwrite(STDERR, sprintf("[%s] SCSS compilation failed: %s\n", date('H:i:s'), $exception->getMessage()));
        }
    }

    usleep(500_000);
}
