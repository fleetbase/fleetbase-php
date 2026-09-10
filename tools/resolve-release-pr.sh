#!/usr/bin/env bash
# GitHub may not expose a merged PR immediately after its push event.
set -euo pipefail

: "${GITHUB_REPOSITORY:?GITHUB_REPOSITORY is required}"
: "${GITHUB_SHA:?GITHUB_SHA is required}"
: "${GITHUB_OUTPUT:?GITHUB_OUTPUT is required}"

for attempt in 1 2 3 4 5 6; do
    lookup_ok=false
    if response=$(gh api --paginate --slurp \
        -H "Accept: application/vnd.github+json" \
        "/repos/$GITHUB_REPOSITORY/commits/$GITHUB_SHA/pulls"); then
        if matches=$(printf '%s' "$response" | jq -ce --arg sha "$GITHUB_SHA" '
            [.[][] | select(.merged_at != null and .base.ref == "main"
                and .merge_commit_sha == $sha and (.head.ref | startswith("release/")))]
        '); then
            lookup_ok=true
            count=$(printf '%s' "$matches" | jq 'length')
            if [ "$count" -gt 1 ]; then
                echo "::error::Multiple release PRs match this merge commit; refusing ambiguous publication." >&2
                exit 1
            fi
            if [ "$count" -eq 1 ]; then
                branch=$(printf '%s' "$matches" | jq -r '.[0].head.ref')
                number=$(printf '%s' "$matches" | jq -r '.[0].number')
                version="${branch#release/}"
                version="${version#v}"
                if ! [[ "$version" =~ ^(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)(-[0-9A-Za-z.-]+)?$ ]]; then
                    echo "Release branch '$branch' must end in a semantic version." >&2
                    exit 1
                fi
                {
                    echo "eligible=true"
                    echo "version=$version"
                    echo "pull-request=$number"
                } >> "$GITHUB_OUTPUT"
                echo "Release $version resolved from PR #$number ($branch), attempt $attempt."
                exit 0
            fi
        fi
    fi
    if [ "$attempt" -lt 6 ]; then
        echo "Release PR not yet resolved (attempt $attempt/6); retrying in 10 seconds."
        sleep 10
    fi
done

if [ "$lookup_ok" != true ]; then
    echo "::error::GitHub release-PR lookup failed after 6 attempts; refusing to treat an API failure as an ineligible commit." >&2
    exit 1
fi
echo "eligible=false" >> "$GITHUB_OUTPUT"
echo "::warning::No merged release/* PR matches this exact main commit after 6 attempts. If this was a release merge, rerun the workflow after GitHub finishes indexing it."
