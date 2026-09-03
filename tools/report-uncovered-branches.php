<?php

/**
 * This file is part of the fleetbase/fleetbase-php library.
 *
 * @copyright Copyright (c) Fleetbase Pte Ltd. <ron@fleetbase.io>
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

use SebastianBergmann\CodeCoverage\CodeCoverage;

require dirname(__DIR__) . '/vendor/autoload.php';

$path = $argv[1] ?? 'build/coverage/coverage.php';
if (!is_string($path) || !is_file($path)) {
    fwrite(STDERR, sprintf("Serialized coverage report not found: %s\n", is_string($path) ? $path : '[invalid]'));
    exit(1);
}

$coverage = require $path;
if (!$coverage instanceof CodeCoverage) {
    fwrite(STDERR, sprintf("Invalid serialized coverage report: %s\n", $path));
    exit(1);
}

$uncovered = [];
foreach ($coverage->getData()->functionCoverage() as $file => $functions) {
    foreach ($functions as $function => $data) {
        foreach ($data['branches'] as $branchId => $branch) {
            if ($branch['hit'] === []) {
                $uncovered[] = sprintf(
                    '%s:%d-%d %s branch %d -> [%s]',
                    $file,
                    $branch['line_start'],
                    $branch['line_end'],
                    $function,
                    $branchId,
                    implode(', ', $branch['out'])
                );
            }
        }
    }
}

if ($uncovered === []) {
    fwrite(STDOUT, "All executable branches are covered.\n");
    exit(0);
}

fwrite(STDOUT, "Uncovered executable branches:\n" . implode("\n", $uncovered) . "\n");
