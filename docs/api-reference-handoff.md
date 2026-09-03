# Fleetbase API-reference handoff

The SDK repository now owns executable examples for every one of the 220 locked Fleetbase and Core API requests. The API-reference repository should consume these reviewed examples instead of maintaining a second hand-written PHP mapping.

Authoritative inputs:

- `contracts/postman-manifest.json` — locked request IDs, verbs, URLs, authentication, fixtures, and source references;
- `contracts/service-map.json` — request-to-service and request-to-method mapping;
- `contracts/php-sdk-examples.json` — machine-readable, stable-ID-keyed PHP calls and complete display snippets;
- `docs/api-examples.md` — generated, executable PHP snippets;
- `docs/api-coverage.md` — the complete human-readable coverage matrix.

The coordinated API-reference change should:

1. Key PHP examples by the stable request ID in the manifest.
2. Render the matching `code` entry from `contracts/php-sdk-examples.json` for Fleetbase and Core requests.
3. Fail its build when a request ID has no SDK example or when a stale request ID remains.
4. Preserve raw HTTP examples alongside PHP rather than using raw HTTP as a silent PHP fallback.
5. Pin the SDK release or reviewed commit used to generate the documentation.
6. Add a fixture test proving all 220 request IDs render a real SDK call.

The coordinated `fleetbase/fleetbase.io` pull request vendors this generated catalog, retains concise calls for canonical CRUD operations, uses exact SDK methods for custom endpoints, and fails generation unless all 220 stable request IDs are consumed. It should be refreshed whenever the SDK contract changes.

Each catalog row includes the variables required by its standalone `code` example. Displayed calls use positional path identifiers and direct API data arrays. Documentation consumers must render the generated call as-is and must not reconstruct the SDK's legacy internal `id`/`body`/`query` envelope.
