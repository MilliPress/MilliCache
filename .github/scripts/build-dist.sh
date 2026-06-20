#!/usr/bin/env bash
# Build the MilliCache dist/ directory: exports tracked files, scopes
# dependencies via Strauss, and regenerates the autoloader. Shared by
# release-bundle.yml, manual-release.yml, and attach-release-asset.yml.
# Run from the repo root. Assumes build/ assets are already present
# (CI commits them; locally you'd run `npm run build` first).
#
# REF (env, default HEAD): git ref whose tree is exported into dist/.
# Lets the attach-release-asset workflow build at a historical tag
# while running the script from a newer branch.
set -euo pipefail

REF="${REF:-HEAD}"
STRAUSS_VERSION="${STRAUSS_VERSION:-0.26.5}"
STRAUSS_URL="https://github.com/BrianHenryIE/strauss/releases/download/${STRAUSS_VERSION}/strauss.phar"

rm -rf dist && mkdir dist

git archive --format=tar "$REF" | tar -x -C dist

# Defensive cleanup: strip dev-only config that slipped past
# export-ignore in older tags (e.g. release-please-config-next.json
# was not covered by the singular pattern in v1.7.0-beta's
# .gitattributes). Safe to run unconditionally — no-op for already-
# clean trees.
rm -f dist/release-please-config*.json

mkdir -p dist/bin

# Download Strauss and verify it actually runs. `curl -f` rejects HTTP errors
# but not a truncated/corrupt 200 response, which used to slip through and then
# fail with a cryptic exit 1 at the `strauss.phar` step below. Verify the phar
# executes (and retry the whole fetch) so a bad download fails here, loudly.
for attempt in 1 2 3; do
	if curl -fsSL --retry 3 --retry-all-errors "$STRAUSS_URL" -o dist/bin/strauss.phar \
		&& php dist/bin/strauss.phar --version >/dev/null 2>&1; then
		break
	fi
	if [ "$attempt" = 3 ]; then
		echo "error: could not obtain a working strauss.phar from $STRAUSS_URL" >&2
		exit 1
	fi
	echo "strauss.phar download/verify failed (attempt $attempt); retrying..." >&2
	sleep 3
done

composer install --no-dev --no-interaction --no-scripts --prefer-dist --working-dir=dist

(cd dist && php bin/strauss.phar -q)

sed -i 's/namespace MilliRules\\Builders;/namespace MilliCache\\Deps\\MilliRules\\Builders;/' dist/stubs/*.php

jq '.autoload.classmap = ["deps/"]' dist/composer.json > dist/composer.json.tmp \
  && mv dist/composer.json.tmp dist/composer.json

composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts --working-dir=dist

rm -rf dist/bin
rm -f dist/composer.json dist/composer.lock

echo "Dist contents:"
ls -la dist/
