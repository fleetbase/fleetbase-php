<?php

/** Point a clean consumer fixture at an extracted, inspected SDK archive. */

declare(strict_types=1);

$fixtureDirectory = isset($argv[1]) && is_string($argv[1]) ? realpath($argv[1]) : false;
$sourceDirectory = isset($argv[2]) && is_string($argv[2]) ? realpath($argv[2]) : false;
if (!is_string($fixtureDirectory) || !is_string($sourceDirectory)) {
    fail('Usage: php tools/configure-consumer-fixture.php <fixture-directory> <sdk-source-directory>');
}

$composerPath = $fixtureDirectory . '/composer.json';
$contents = file_get_contents($composerPath);
$composer = is_string($contents) ? json_decode($contents, true) : null;
if (!is_array($composer)) {
    fail('The consumer fixture has no valid composer.json.');
}

$composer['repositories'] = [[
    'type' => 'path',
    'url' => $sourceDirectory,
    'options' => [
        'symlink' => false,
        'versions' => ['fleetbase/fleetbase-php' => '1.1.0-dev'],
    ],
]];

$json = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (!is_string($json) || file_put_contents($composerPath, $json . "\n") === false) {
    fail('Unable to configure the archive-backed consumer fixture.');
}

printf("Configured %s to install SDK source from %s.\n", basename($fixtureDirectory), $sourceDirectory);

function fail(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}
