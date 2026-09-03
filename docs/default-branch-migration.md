# Default branch migration: `master` to `main`

This is the maintainer runbook for the repository-setting change. Complete it only after the release integration pull request is approved and all required checks are green. The SDK workflows already accept both branch names during the transition.

## Before changing GitHub settings

1. Confirm the reviewed release-integration commit is the desired tip for both branches.
2. Create `main` from that exact commit and push it without rewriting `master`.
3. Configure the protected `release` environment and its required approvers.
4. Add required checks for quality/contracts, the PHP compatibility matrix, exact coverage, mutation baseline, archive consumers, Composer audit/license metadata, dependency review, secret scanning, and CodeQL.
5. Add the `POSTMAN_API_KEY` Actions secret used only by the disposable official-collection workflow.
6. Confirm the Packagist webhook owner and target repository.

## Change the default branch

In GitHub, open **Settings → Branches → Default branch**, choose `main`, and confirm. Do not delete or force-update `master` during this change.

Immediately verify:

- a new pull request targets `main` by default;
- CI and security workflows run on a push to `main`;
- the API contract-drift and disposable-contract workflows are visible in Actions and can be manually dispatched;
- release validation rejects publishing from every branch except `main`;
- CodeQL/default setup, Dependabot, repository rules, badges, and external integrations use `main`;
- Packagist exposes `dev-main` and its webhook still receives events;
- a clean clone checks out `main`.

## Consumer transition

Keep `master` protected and read-only during the agreed transition period. Add a final pointer commit only if maintainers decide consumers need one; do not continually mirror changes between the branches. Consumers following tagged releases are unaffected.

For existing local clones:

```bash
git fetch origin
git branch -m master main
git branch --set-upstream-to=origin/main main
git remote set-head origin -a
```

Remove `master` only after repository search, webhook review, Packagist verification, documentation checks, and the agreed transition window show that no maintained integration still depends on it.

## Rollback

If a required integration fails, set the GitHub default branch back to `master` and repair the integration without rewriting either branch. A default-branch setting change does not require deleting `main` or changing published tags.
