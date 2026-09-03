<?php

/** Verify that the disposable-stack run invoked every locked SDK contract method. */

declare(strict_types=1);

$options = getopt('', ['state:']);
$statePath = is_string($options['state'] ?? null) ? $options['state'] : 'build/live-contract/state.json';
$manifest = json_decode((string) file_get_contents(dirname(__DIR__) . '/contracts/postman-manifest.json'), true);
$state = is_file($statePath) ? json_decode((string) file_get_contents($statePath), true) : [];
if (!is_array($manifest) || !is_array($manifest['requests'] ?? null) || !is_array($state)) {
    fail('Unable to read live SDK contract evidence.');
}

$results = is_array($state['requests'] ?? null) ? $state['requests'] : [];
$missing = [];
$withoutResponse = [];
$invalidAttempts = [];
foreach ($manifest['requests'] as $request) {
    if (!is_array($request) || !is_string($request['id'] ?? null)) {
        fail('The SDK contract manifest is invalid.');
    }
    $id = $request['id'];
    if (!array_key_exists($id, $results)) {
        $missing[] = $id;
    } elseif (!is_int($results[$id]['status'] ?? null)) {
        $withoutResponse[] = $id;
    } elseif (($results[$id]['attempted'] ?? null) !== true
        || !is_int($results[$id]['attempts'] ?? null)
        || $results[$id]['attempts'] < 1) {
        $invalidAttempts[] = $id;
    }
}

printf("Live SDK contract evidence: %d/%d requests invoked.\n", count($results), count($manifest['requests']));
if ($missing !== []) {
    fail('Missing SDK invocations: ' . implode(', ', $missing));
}
if ($withoutResponse !== []) {
    fail('SDK invocations without an API response: ' . implode(', ', $withoutResponse));
}
if ($invalidAttempts !== []) {
    fail('SDK invocations with invalid attempt evidence: ' . implode(', ', $invalidAttempts));
}
if (count($results) !== count($manifest['requests'])) {
    fail('Live SDK evidence contains unexpected request IDs.');
}

fwrite(STDOUT, "All locked Postman requests executed through their PHP SDK methods.\n");

function fail(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}
