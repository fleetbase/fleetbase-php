<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$version = PHPUnit\Runner\Version::id();
$configuration = version_compare($version, '10.0.0', '<') ? 'phpunit-9.xml.dist' : 'phpunit.xml.dist';
$command = escapeshellarg(PHP_BINARY)
    . ' ' . escapeshellarg(dirname(__DIR__) . '/vendor/bin/phpunit')
    . ' --configuration=' . escapeshellarg($configuration);

foreach (array_slice($argv, 1) as $argument) {
    $command .= ' ' . escapeshellarg($argument);
}

passthru($command, $exitCode);
exit($exitCode);
