<?php

/** Execute terminating legacy helpers and merge their real Xdebug data into PHPUnit coverage. */

declare(strict_types=1);

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Report\Clover;
use SebastianBergmann\CodeCoverage\Report\Cobertura;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;
use SebastianBergmann\CodeCoverage\Report\Text;
use SebastianBergmann\CodeCoverage\Report\Thresholds;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
$coveragePath = $root . '/build/coverage/coverage.php';
$rawPath = $root . '/build/coverage/terminating-helper.raw';
$command = [PHP_BINARY, $root . '/tools/capture-terminating-coverage-child.php', $rawPath];
$descriptorSpec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];
$environment = array_merge($_ENV, ['XDEBUG_MODE' => 'coverage']);
$process = proc_open($command, $descriptorSpec, $pipes, $root, $environment);
if (!is_resource($process)) {
    fail('Unable to start the terminating-helper coverage process.');
}

fclose($pipes[0]);
$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$exitCode = proc_close($process);

if ($exitCode !== 1 || !is_string($stdout) || strpos($stdout, 'fleetbase-terminating-coverage') === false) {
    fail(sprintf('Terminating-helper probe failed with exit %d: %s%s', $exitCode, $stdout ?: '', $stderr ?: ''));
}

$serializedRaw = file_get_contents($rawPath);
$raw = is_string($serializedRaw) ? unserialize($serializedRaw, ['allowed_classes' => false]) : false;
if (!is_array($raw)) {
    fail('Unable to load terminating-helper Xdebug coverage.');
}

$coverage = is_file($coveragePath) ? require $coveragePath : null;
if (!$coverage instanceof CodeCoverage) {
    fail('Unable to load PHPUnit coverage for merging.');
}

$coverage->append(RawCodeCoverageData::fromXdebugWithPathCoverage($raw), 'terminating-helper');

$serializedCoverage = serialize($coverage);
$php = "<?php\nreturn unserialize(" . var_export($serializedCoverage, true) . ");\n";
if (file_put_contents($coveragePath, $php) === false) {
    fail('Unable to rewrite merged PHPUnit coverage.');
}

(new Clover())->process($coverage, $root . '/build/coverage/clover.xml');
(new Cobertura())->process($coverage, $root . '/build/coverage/cobertura.xml');
(new HtmlReport())->process($coverage, $root . '/build/coverage/html');
$summary = (new Text(Thresholds::default(), false, false))->process($coverage, false);
if (file_put_contents($root . '/build/coverage/summary.txt', $summary) === false) {
    fail('Unable to rewrite merged text coverage.');
}

@unlink($rawPath);
fwrite(STDOUT, "Merged terminating-helper coverage.\n");

function fail(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}
