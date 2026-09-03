<?php

/** Verify release-license metadata and source notices. */

declare(strict_types=1);

$root = dirname(__DIR__);
$composer = json_decode((string) file_get_contents($root . '/composer.json'), true);
if (!is_array($composer) || ($composer['license'] ?? null) !== 'AGPL-3.0-or-later') {
    fail('composer.json must declare AGPL-3.0-or-later.');
}

$license = (string) file_get_contents($root . '/LICENSE');
if (strpos($license, 'GNU AFFERO GENERAL PUBLIC LICENSE') === false || strpos($license, 'Version 3, 19 November 2007') === false) {
    fail('LICENSE is not the canonical GNU Affero General Public License v3 text.');
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/src'));
foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    if (strpos((string) file_get_contents($file->getPathname()), 'AGPL-3.0-or-later') === false) {
        fail(sprintf('Missing AGPL-3.0-or-later notice: %s', $file->getPathname()));
    }
}

foreach (['README.md', 'CHANGELOG.md', 'docs/migration-guide.md'] as $relativePath) {
    if (strpos((string) file_get_contents($root . '/' . $relativePath), 'AGPL-3.0-or-later') === false) {
        fail(sprintf('Missing release license notice: %s', $relativePath));
    }
}

fwrite(STDOUT, "AGPL-3.0-or-later metadata and source notices verified.\n");

function fail(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}
