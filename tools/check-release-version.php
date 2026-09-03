<?php

/** Validate a requested release version against tags and reviewed notes. */

declare(strict_types=1);

$version = $argv[1] ?? null;
$allowNonMain = in_array('--allow-non-main', $argv, true);
if (!is_string($version) || preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-[0-9A-Za-z.-]+)?$/', $version) !== 1) {
    fail('Release version must be a semantic version without a v prefix.');
}

$root = dirname(__DIR__);
chdir($root);
$branch = getenv('GITHUB_REF_NAME') ?: trim(command('git branch --show-current'));
if (!$allowNonMain && $branch !== 'main') {
    fail(sprintf('Publishing is allowed from main only; current branch is %s.', $branch !== '' ? $branch : '[detached]'));
}
if (trim(command('git status --porcelain')) !== '') {
    fail('Release worktree must be clean.');
}

$tags = array_filter(explode("\n", trim(command('git tag --list'))));
if (in_array($version, $tags, true) || in_array('v' . $version, $tags, true)) {
    fail(sprintf('Release tag %s already exists.', $version));
}
$stableTags = array_values(array_filter($tags, static function (string $tag): bool {
    return preg_match('/^v?\d+\.\d+\.\d+$/', $tag) === 1;
}));
usort($stableTags, static function (string $left, string $right): int {
    return version_compare(ltrim($right, 'v'), ltrim($left, 'v'));
});
$latest = $stableTags[0] ?? null;
if (is_string($latest) && version_compare($version, ltrim($latest, 'v'), '<=')) {
    fail(sprintf('Release version %s must be greater than latest tag %s.', $version, $latest));
}

$changelog = (string) file_get_contents($root . '/CHANGELOG.md');
if (preg_match('/^## \[' . preg_quote($version, '/') . '\] - \d{4}-\d{2}-\d{2}$/m', $changelog) !== 1) {
    fail(sprintf('CHANGELOG.md has no dated %s release section.', $version));
}
$notesPath = $root . '/docs/releases/' . $version . '.md';
if (!is_file($notesPath) || strpos((string) file_get_contents($notesPath), 'AGPL-3.0-or-later') === false) {
    fail(sprintf('Release notes must exist at docs/releases/%s.md and explain AGPL-3.0-or-later.', $version));
}

printf("Release version %s verified against latest tag %s.\n", $version, $latest ?? '[none]');

function command(string $command): string
{
    $output = [];
    $status = 0;
    exec($command, $output, $status);
    if ($status !== 0) {
        fail(sprintf('Release check command failed: %s', $command));
    }
    return implode("\n", $output);
}

function fail(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}
