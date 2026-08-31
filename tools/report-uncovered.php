<?php

/**
 * This file is part of the fleetbase/fleetbase-php library.
 *
 * @copyright Copyright (c) Fleetbase Pte Ltd. <ron@fleetbase.io>
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

$path = $argv[1] ?? 'build/coverage/clover.xml';
if (!is_string($path) || !is_file($path)) {
    fwrite(STDERR, sprintf("Coverage report not found: %s\n", is_string($path) ? $path : '[invalid]'));
    exit(1);
}

$document = new DOMDocument();
if (!$document->load($path)) {
    fwrite(STDERR, sprintf("Unable to parse coverage report: %s\n", $path));
    exit(1);
}

$uncovered = [];
/** @var DOMElement $file */
foreach ($document->getElementsByTagName('file') as $file) {
    $name = $file->getAttribute('name');
    $lines = [];
    /** @var DOMElement $line */
    foreach ($file->getElementsByTagName('line') as $line) {
        if ($line->getAttribute('type') === 'stmt' && (int) $line->getAttribute('count') === 0) {
            $lines[] = (int) $line->getAttribute('num');
        }
    }
    if ($lines !== []) {
        $uncovered[$name] = $lines;
    }
}

if ($uncovered === []) {
    fwrite(STDOUT, "All executable lines are covered.\n");
    exit(0);
}

fwrite(STDOUT, "Uncovered executable lines:\n");
foreach ($uncovered as $file => $lines) {
    fwrite(STDOUT, sprintf("%s: %s\n", $file, implode(', ', $lines)));
}
