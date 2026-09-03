<?php

/** Inspect a release archive for required files and forbidden development state. */

declare(strict_types=1);

$path = $argv[1] ?? null;
if (!is_string($path) || !is_file($path)) {
    fail('Usage: php tools/check-release-archive.php <archive.tar.gz>');
}

$archive = new PharData($path);
$entries = [];
foreach (new RecursiveIteratorIterator($archive) as $file) {
    if ($file instanceof SplFileInfo && $file->isFile()) {
        $entry = preg_replace('#^phar://.+\.tar\.gz/#', '', $file->getPathname());
        if (!is_string($entry)) {
            fail('Unable to normalize a release archive entry.');
        }
        $entries[] = $entry;
    }
}

$forbidden = [
    '#/(?:vendor|build|tests|tools|docs|contracts|fixtures)/#',
    '#/(?:\.env(?:\..*)?|\.phpunit\.result\.cache|composer\.lock)$#',
    '#/(?:\.github|\.git)(?:/|$)#',
    '#(?:^|/)Users/[^/]+/#',
];
foreach ($entries as $entry) {
    foreach ($forbidden as $pattern) {
        if (preg_match($pattern, '/' . $entry) === 1) {
            fail(sprintf('Forbidden release archive entry: %s', $entry));
        }
    }
}

foreach (['LICENSE', 'README.md', 'composer.json', 'src/Fleetbase.php'] as $required) {
    $matches = array_filter($entries, static function (string $entry) use ($required): bool {
        return substr($entry, -strlen('/' . $required)) === '/' . $required;
    });
    if (count($matches) !== 1) {
        fail(sprintf('Release archive must contain exactly one %s.', $required));
    }
}

printf("Release archive verified: %d files, no forbidden development state.\n", count($entries));

function fail(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}
