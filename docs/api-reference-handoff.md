# Fleetbase API-reference handoff

The SDK repository now owns executable examples for every one of the 220 locked Fleetbase and Core API requests. The API-reference repository should consume these reviewed examples instead of maintaining a second hand-written PHP mapping.

Authoritative inputs:

- `contracts/postman-manifest.json` — locked request IDs, verbs, URLs, authentication, fixtures, and source references;
- `contracts/service-map.json` — request-to-service and request-to-method mapping;
- `docs/api-examples.md` — generated, executable PHP snippets;
- `docs/api-coverage.md` — the complete human-readable coverage matrix.

The coordinated API-reference change should:

1. Key PHP examples by the stable request ID in the manifest.
2. Render the matching fenced snippet from `docs/api-examples.md` for Fleetbase and Core requests.
3. Fail its build when a request ID has no SDK example or when a stale request ID remains.
4. Preserve raw HTTP examples alongside PHP rather than using raw HTTP as a silent PHP fallback.
5. Pin the SDK release or reviewed commit used to generate the documentation.
6. Add a fixture test proving all 220 request IDs render a real SDK call.

The API-reference repository is outside this SDK change set. Its pull request requires explicit maintainer authorization and should reference the reviewed SDK release commit.
