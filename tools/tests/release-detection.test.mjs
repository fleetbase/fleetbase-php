import assert from 'node:assert/strict';
import { mkdtempSync, writeFileSync, readFileSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { spawnSync } from 'node:child_process';
import { test } from 'node:test';

const sha = 'a'.repeat(40);
const release = {
    number: 11, merged_at: '2026-09-10T03:05:16Z', merge_commit_sha: sha,
    base: { ref: 'main' }, head: { ref: 'release/v1.3.0' },
};

function detect(responses) {
    const directory = mkdtempSync(join(tmpdir(), 'sdk-release-detection-'));
    try {
        writeFileSync(join(directory, 'responses.json'), JSON.stringify(responses));
        writeFileSync(join(directory, 'count'), '0');
        writeFileSync(join(directory, 'output'), '');
        writeFileSync(join(directory, 'gh'), `#!/usr/bin/env node
const fs = require('node:fs');
const path = process.env.FIXTURE_PATH;
const count = Number(fs.readFileSync(path + '/count', 'utf8'));
fs.writeFileSync(path + '/count', String(count + 1));
const args = process.argv.slice(2);
if (!args.includes('--paginate') || !args.includes('--slurp') || !args.includes('/repos/fleetbase/fleetbase-php/commits/' + process.env.GITHUB_SHA + '/pulls')) process.exit(98);
const responses = JSON.parse(fs.readFileSync(path + '/responses.json', 'utf8'));
const response = responses[Math.min(count, responses.length - 1)];
if (response === 'api-error') process.exit(1);
process.stdout.write(response === 'malformed' ? '{' : JSON.stringify(response));
`, { mode: 0o755 });
        writeFileSync(join(directory, 'sleep'), '#!/bin/sh\nexit 0\n', { mode: 0o755 });
        const result = spawnSync('bash', [resolve('tools/resolve-release-pr.sh')], {
            encoding: 'utf8', timeout: 15000,
            env: {
                ...process.env, PATH: `${directory}:${process.env.PATH}`, FIXTURE_PATH: directory,
                GITHUB_REPOSITORY: 'fleetbase/fleetbase-php', GITHUB_SHA: sha,
                GITHUB_OUTPUT: join(directory, 'output'),
            },
        });
        assert.ifError(result.error);
        return { ...result, output: readFileSync(join(directory, 'output'), 'utf8'), attempts: Number(readFileSync(join(directory, 'count'), 'utf8')) };
    } finally {
        rmSync(directory, { recursive: true, force: true });
    }
}

test('resolves an immediately available release', () => {
    const result = detect([[[release]]]);
    assert.equal(result.status, 0);
    assert.equal(result.attempts, 1);
    assert.equal(result.output, 'eligible=true\nversion=1.3.0\npull-request=11\n');
});

test('retries empty and not-yet-merged API results', () => {
    const result = detect([[[]], [[{ ...release, merged_at: null }]], [[release]]]);
    assert.equal(result.status, 0);
    assert.equal(result.attempts, 3);
    assert.match(result.output, /eligible=true/);
});

test('accepts a release on the final bounded attempt', () => {
    const result = detect([[[]], [[]], [[]], [[]], [[]], [[release]]]);
    assert.equal(result.status, 0);
    assert.equal(result.attempts, 6);
    assert.match(result.output, /eligible=true/);
});

test('handles pagination and versions without a v prefix', () => {
    const result = detect([[[], [{ ...release, head: { ref: 'release/1.3.0-rc.1' } }]]]);
    assert.equal(result.status, 0);
    assert.match(result.output, /version=1.3.0-rc.1/);
});

test('does not publish ordinary, unmerged, wrong-base, or historical PRs', () => {
    const result = detect([[
        [{ ...release, head: { ref: 'feature/fix' } }, { ...release, merged_at: null }],
        [{ ...release, base: { ref: 'develop' } }, { ...release, merge_commit_sha: 'b'.repeat(40) }],
    ]]);
    assert.equal(result.status, 0);
    assert.equal(result.attempts, 6);
    assert.equal(result.output, 'eligible=false\n');
});

test('warns when the association stays empty through exhaustion', () => {
    const result = detect([[[]]]);
    assert.equal(result.status, 0);
    assert.equal(result.attempts, 6);
    assert.equal(result.output, 'eligible=false\n');
    assert.match(result.stdout, /::warning::/);
});

test('recovers from API errors and malformed JSON', () => {
    const result = detect(['api-error', 'malformed', [[release]]]);
    assert.equal(result.status, 0);
    assert.equal(result.attempts, 3);
    assert.match(result.output, /eligible=true/);
});

for (const response of ['api-error', 'malformed']) {
    test(`fails closed on exhausted ${response}`, () => {
        const result = detect([response]);
        assert.equal(result.status, 1);
        assert.equal(result.attempts, 6);
        assert.equal(result.output, '');
        assert.match(result.stderr, /::error::/);
    });
}

test('rejects invalid release versions', () => {
    const result = detect([[[{ ...release, head: { ref: 'release/not-a-version' } }]]]);
    assert.equal(result.status, 1);
    assert.equal(result.output, '');
});

test('fails closed on ambiguous matching release PRs', () => {
    const result = detect([[[release, { ...release, number: 12 }]]]);
    assert.equal(result.status, 1);
    assert.equal(result.output, '');
    assert.match(result.stderr, /ambiguous/);
});
