<?php

/** Enforce exact aggregate line and branch coverage from PHPUnit's text report. */

declare(strict_types=1);

$path = $argv[1] ?? 'build/coverage/summary.txt';
$required = isset($argv[2]) && is_numeric($argv[2]) ? (float) $argv[2] : 100.0;
$contents = is_string($path) ? file_get_contents($path) : false;
if (!is_string($contents)) {
    fail('Unable to read the PHPUnit coverage summary.');
}

foreach (['Branches', 'Lines'] as $metric) {
    if (preg_match('/^\s*' . $metric . ':\s+([0-9.]+)%/m', $contents, $matches) !== 1) {
        fail(sprintf('Coverage summary has no aggregate %s metric.', strtolower($metric)));
    }
    $actual = (float) $matches[1];
    if ($actual + 0.00001 < $required) {
        fail(sprintf('%s coverage is %.2f%%; %.2f%% is required.', $metric, $actual, $required));
    }
    printf("%s coverage: %.2f%%\n", $metric, $actual);
}

function fail(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}
