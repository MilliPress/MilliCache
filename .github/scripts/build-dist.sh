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

mkdir -p dist/bin
curl -sL "$STRAUSS_URL" -o dist/bin/strauss.phar

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
