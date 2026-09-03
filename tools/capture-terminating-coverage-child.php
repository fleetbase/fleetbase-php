<?php

/** Capture Xdebug path coverage for legacy helpers that intentionally terminate. */

declare(strict_types=1);

use Fleetbase\Sdk\Utils;

require dirname(__DIR__) . '/vendor/autoload.php';

$output = $argv[1] ?? null;
if (!is_string($output) || $output === '') {
    fwrite(STDERR, "A raw coverage output path is required.\n");
    exit(2);
}

if (!function_exists('xdebug_start_code_coverage')) {
    fwrite(STDERR, "Xdebug coverage is required.\n");
    exit(2);
}

register_shutdown_function(static function () use ($output): void {
    $coverage = xdebug_get_code_coverage();
    $serialized = serialize($coverage);
    if (file_put_contents($output, $serialized) === false) {
        fwrite(STDERR, "Unable to write terminating-helper coverage.\n");
    }
});

xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE | XDEBUG_CC_BRANCH_CHECK);
Utils::dd('fleetbase-terminating-coverage');
