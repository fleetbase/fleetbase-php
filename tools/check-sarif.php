<?php

/** Fail when a SARIF report contains warning- or error-level findings. */

declare(strict_types=1);

$path = $argv[1] ?? null;
$contents = is_string($path) ? file_get_contents($path) : false;
$sarif = is_string($contents) ? json_decode($contents, true) : null;
if (!is_array($sarif) || !is_array($sarif['runs'] ?? null)) {
    fail('Usage: php tools/check-sarif.php <report.sarif>');
}

$findings = [];
foreach ($sarif['runs'] as $run) {
    if (!is_array($run)) {
        continue;
    }
    foreach (is_array($run['results'] ?? null) ? $run['results'] : [] as $result) {
        if (!is_array($result)) {
            continue;
        }
        $level = is_string($result['level'] ?? null) ? $result['level'] : 'warning';
        if (!in_array($level, ['warning', 'error'], true)) {
            continue;
        }
        $rule = is_string($result['ruleId'] ?? null) ? $result['ruleId'] : '[unknown rule]';
        $message = $result['message']['text'] ?? '[no message]';
        $findings[] = sprintf('%s (%s): %s', $rule, $level, is_string($message) ? $message : '[no message]');
    }
}

if ($findings !== []) {
    fail("CodeQL reported actionable findings:\n- " . implode("\n- ", $findings));
}

fwrite(STDOUT, "CodeQL SARIF contains no warning- or error-level findings.\n");

function fail(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}
