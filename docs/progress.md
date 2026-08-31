# v1.1.0 implementation progress

This log records completed checkpoints and evidence. A passing local check is not represented as CI success.

## 2026-08-31 — baseline refresh and contract freeze

- Refreshed official Git refs and found published 1.0.3, which was absent from the stale local `origin/master` reference.
- Merged current `origin/master` into `release/v1.1.0` at `e4a368d`; retained 1.0.2 as the required compatibility baseline.
- Locked Postman, Core API, Fleet-Ops, 1.0.2, and 1.0.3 commits in `contracts/contract-lock.json`.
- Generated public/protected API snapshots: 1.0.2 has 22 classes, 64 declared public/protected methods, and 13 facade runtime properties; 1.0.3 has 22 classes, 84 declared public/protected methods, and 13 facade runtime properties.
- Generated the Postman contract manifest: 220 requests across 36 groups (`84 GET`, `77 POST`, `25 PUT`, `6 PATCH`, `28 DELETE`). All begin as explicitly unmapped.
- Added six architecture decisions and the non-secret repository/release audit.
- Local generator syntax checks: pass on PHP 8.2.10.
- Deterministic manifest count check: pass (220 requests, 36 groups).
- Working tree passes the 1.0.3 public API check. The 1.0.2 check correctly detects the published `getDistanceAndTime()` parameter rename in 1.0.3; the compatibility implementation must restore the legacy named argument while accepting the 1.0.3 query form.

Remaining in Phase 0: characterize observable legacy behavior, add automated snapshot/manifest verification, establish backwards-compatibility tooling, and receive maintainer confirmation of relicensing rights. Phase 1 repository hygiene can proceed independently of the human licensing confirmation, but the new license cannot be published until confirmation.
