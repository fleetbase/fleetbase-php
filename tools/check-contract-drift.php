<?php

/** Compare current Postman request contracts with the locked SDK inventory. */

declare(strict_types=1);

$options = getopt('', ['locked:', 'candidate:', 'output:']);
$lockedPath = is_string($options['locked'] ?? null) ? $options['locked'] : 'contracts/postman-manifest.json';
$candidatePath = is_string($options['candidate'] ?? null) ? $options['candidate'] : 'build/contract-drift.json';
$outputPath = is_string($options['output'] ?? null) ? $options['output'] : 'build/contract-drift.md';
$locked = requests($lockedPath);
$candidate = requests($candidatePath);

$added = array_values(array_diff(array_keys($candidate), array_keys($locked)));
$removed = array_values(array_diff(array_keys($locked), array_keys($candidate)));
$changed = [];
foreach (array_intersect(array_keys($locked), array_keys($candidate)) as $id) {
    if (canonical($locked[$id]) !== canonical($candidate[$id])) {
        $changed[] = $id;
    }
}
sort($added);
sort($removed);
sort($changed);

$candidateContents = file_get_contents($candidatePath);
$candidateDocument = is_string($candidateContents) ? json_decode($candidateContents, true) : null;
$candidateRef = is_array($candidateDocument) && is_string($candidateDocument['source']['ref'] ?? null)
    ? $candidateDocument['source']['ref']
    : '[unknown]';
$lines = [
    '# Fleetbase Postman contract drift',
    '',
    sprintf('Compared the SDK lock with `fleetbase/postman@%s`.', $candidateRef),
    '',
    sprintf('- Added requests: %d', count($added)),
    sprintf('- Removed requests: %d', count($removed)),
    sprintf('- Changed requests: %d', count($changed)),
];
foreach (['Added' => $added, 'Removed' => $removed, 'Changed' => $changed] as $heading => $ids) {
    if ($ids === []) {
        continue;
    }
    $lines[] = '';
    $lines[] = '## ' . $heading;
    $lines[] = '';
    foreach ($ids as $id) {
        $lines[] = '- `' . $id . '`';
    }
}

$directory = dirname($outputPath);
if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
    fail('Unable to create the contract drift report directory.');
}
if (file_put_contents($outputPath, implode("\n", $lines) . "\n") === false) {
    fail('Unable to write the contract drift report.');
}

if ($added !== [] || $removed !== [] || $changed !== []) {
    fail(sprintf('Postman contract drift detected: %d added, %d removed, %d changed.', count($added), count($removed), count($changed)));
}

printf("No Postman contract drift across %d requests.\n", count($locked));

/** @return array<string, array<string, mixed>> */
function requests(string $path): array
{
    $contents = file_get_contents($path);
    $document = is_string($contents) ? json_decode($contents, true) : null;
    if (!is_array($document) || !is_array($document['requests'] ?? null)) {
        fail(sprintf('Unable to read contract requests from %s.', $path));
    }
    $indexed = [];
    foreach ($document['requests'] as $request) {
        if (!is_array($request) || !is_string($request['id'] ?? null)) {
            fail(sprintf('Contract request in %s has no ID.', $path));
        }
        $indexed[$request['id']] = $request;
    }
    ksort($indexed);
    return $indexed;
}

/** @param array<string, mixed> $request */
function canonical(array $request): string
{
    $contract = [];
    foreach (['collection', 'group', 'name', 'method', 'url', 'source', 'authentication', 'response_fixtures'] as $field) {
        $contract[$field] = $request[$field] ?? null;
    }
    $contract['request_fixture'] = normalizedFixture($request['request_fixture'] ?? null);
    $json = json_encode($contract, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (!is_string($json)) {
        fail('Unable to canonicalize a contract request.');
    }
    return $json;
}

/** @param mixed $value @return mixed */
function normalizedFixture($value)
{
    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = normalizedFixture($item);
        }
        return $value;
    }
    if (is_string($value) && (substr($value, -8) === '-fixture' || preg_match('/^\{\{[^}]+\}\}$/', $value) === 1)) {
        return '<fixture>';
    }
    return $value;
}

function fail(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}
