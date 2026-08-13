<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

function assertSame(mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf(
            'Expected %s, received %s.',
            var_export($expected, true),
            var_export($actual, true),
        ));
    }
}

/**
 * @param callable(): mixed $callback
 * @param class-string<Throwable> $expectedClass
 */
function assertThrows(callable $callback, string $expectedClass): Throwable
{
    try {
        $callback();
    } catch (Throwable $exception) {
        if (!$exception instanceof $expectedClass) {
            throw new RuntimeException(sprintf(
                'Expected %s, received %s.',
                $expectedClass,
                $exception::class,
            ), previous: $exception);
        }

        return $exception;
    }

    throw new RuntimeException(sprintf('Expected %s to be thrown.', $expectedClass));
}

$testFiles = glob(__DIR__ . '/*Test.php');
$tests = [];

foreach ($testFiles === false ? [] : $testFiles as $testFile) {
    $fileTests = require $testFile;

    if (!is_array($fileTests)) {
        throw new RuntimeException(sprintf('Test file must return an array: %s', $testFile));
    }

    $tests = array_merge($tests, $fileTests);
}

$failures = 0;

foreach ($tests as $name => $test) {
    try {
        $test();
        fwrite(STDOUT, sprintf("PASS %s\n", $name));
    } catch (Throwable $exception) {
        $failures++;
        fwrite(STDERR, sprintf("FAIL %s: %s\n", $name, $exception->getMessage()));
    }
}

fwrite(STDOUT, sprintf("\n%d tests, %d failures\n", count($tests), $failures));

exit($failures === 0 ? 0 : 1);
