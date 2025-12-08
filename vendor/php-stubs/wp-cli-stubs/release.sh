#!/usr/bin/env bash

set -e

for V in 2.1.0  2.2.0  2.3.0  2.4.0  2.5.0  2.6.0  2.7.0  2.8.0  2.9.0 2.10.0 \
         2.11.0 2.12.0; do
    echo "Releasing version ${V} ..."

    if git rev-parse "refs/tags/v${V}" >/dev/null 2>&1; then
        echo "Tag exists!"
        continue;
    fi

    # Get composer.lock from wp-cli/wp-cli-bundle
    wget -nv -O "source/composer.lock" "https://github.com/wp-cli/wp-cli-bundle/raw/v${V}/composer.lock"
    printf -v SED_EXP 's#\\("version"\\): "[0-9]\\+\\.[0-9]\\+\\.[0-9]\\+"#\\1: "%s"#' "${V}"
    sed -i -e "$SED_EXP" source/composer.json
    composer --working-dir=source/ install --no-interaction --no-dev

    # Generate stubs
    echo "Generating stubs ..."
    ./generate.sh

    # Tag version
    git commit --all -m "Generate stubs for WP-CLI ${V}"
    git tag "v${V}"
done
