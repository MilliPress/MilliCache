#!/usr/bin/env bash
#
# wp-env afterStart lifecycle script for MilliCache.
# Runs after every `wp-env start`. Must be fully idempotent.
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"
COMPOSE_FILE="$SCRIPT_DIR/docker-compose.custom.yml"

echo "=== MilliCache afterStart ==="

# ---------------------------------------------------------------------------
# 1. Determine the wp-env Docker network name
# ---------------------------------------------------------------------------
# The network is named <project>_default where <project> is the basename of
# the wp-env install path (an MD5 hash of the .wp-env.json path).
INSTALL_PATH=$(npx wp-env status --json 2>/dev/null \
  | node -e "let d='';process.stdin.on('data',c=>d+=c);process.stdin.on('end',()=>console.log(JSON.parse(d).installPath))" 2>/dev/null)

if [ -z "$INSTALL_PATH" ]; then
  echo "Error: Could not determine wp-env install path"
  exit 1
fi

PROJECT_NAME=$(basename "$INSTALL_PATH")
export WP_ENV_NETWORK="${PROJECT_NAME}_default"

echo "Docker network: $WP_ENV_NETWORK"

# ---------------------------------------------------------------------------
# 2. Start Redis (and optionally KeyDB / Dragonfly for local dev)
# ---------------------------------------------------------------------------
if [ "${CI:-}" = "true" ]; then
  SERVICES="redis"
else
  SERVICES="redis keydb dragonfly"
fi

echo "Starting services: $SERVICES"
# shellcheck disable=SC2086
docker compose -f "$COMPOSE_FILE" up -d $SERVICES

# ---------------------------------------------------------------------------
# 3. Wait for the database to be ready
# ---------------------------------------------------------------------------
echo "Waiting for database..."
for attempt in $(seq 1 15); do
  if npx wp-env run cli wp db check >/dev/null 2>&1; then
    echo "Database ready"
    break
  fi
  delay=$(( attempt * 2 ))
  [ "$delay" -gt 10 ] && delay=10
  echo "  Attempt $attempt/15: waiting ${delay}s..."
  sleep "$delay"
done

# ---------------------------------------------------------------------------
# 4. Flush Redis cache (idempotent — ensures clean state after reset)
# ---------------------------------------------------------------------------
npx wp-env run cli bash -c "redis-cli -h redis FLUSHALL" >/dev/null 2>&1 || true

# ---------------------------------------------------------------------------
# 5. Install redis-cli on CLI containers (idempotent)
# ---------------------------------------------------------------------------
echo "Checking redis-cli..."
if npx wp-env run cli bash -c "which redis-cli" >/dev/null 2>&1; then
  echo "  redis-cli already present"
else
  echo "  Installing redis-cli..."
  npx wp-env run cli bash -c "sudo apk add --update redis" >/dev/null 2>&1 || true
fi

# ---------------------------------------------------------------------------
# 6. Create multisite subsites (idempotent — fails silently if they exist)
# ---------------------------------------------------------------------------
echo "Ensuring multisite subsites..."
for i in 2 3 4 5; do
  npx wp-env run cli wp site create \
    --slug="site${i}" --title="Site ${i}" --email="site${i}@admin.local" \
    >/dev/null 2>&1 || true
done

# ---------------------------------------------------------------------------
# 7. Configure permalinks (/%postname%/)
#    wp-env 11 defaults to day-and-name; tests expect short postname slugs.
# ---------------------------------------------------------------------------
echo "Configuring permalinks..."
npx wp-env run cli wp option update permalink_structure '/%postname%/' >/dev/null 2>&1 || true
npx wp-env run cli wp rewrite flush --hard >/dev/null 2>&1 || true

# Restore .htaccess (WordPress may have overwritten the mapped file)
cd "$PROJECT_DIR"
git checkout -- tests/wp-env/.htaccess 2>/dev/null || true

# ---------------------------------------------------------------------------
# 8. Import sample content (idempotent — skips if posts already exist)
# ---------------------------------------------------------------------------
echo "Importing sample content..."
CONTENT_PATH="/var/www/html/wp-content/plugins/millicache/tests/wp-env/sample-content.xml"

import_site_content() {
  local site_flag="$1"
  local post_count
  post_count=$(npx wp-env run cli wp post list --post_type=post --format=count $site_flag 2>/dev/null || echo "0")

  if [ "$post_count" -gt 1 ] 2>/dev/null; then
    return 0  # Content already exists
  fi

  npx wp-env run cli wp plugin install wordpress-importer --activate $site_flag >/dev/null 2>&1 || true
  npx wp-env run cli wp import "$CONTENT_PATH" --authors=create $site_flag >/dev/null 2>&1 || true
  npx wp-env run cli wp rewrite flush $site_flag >/dev/null 2>&1 || true
  echo "  Imported content${site_flag:+ ($site_flag)}"
}

import_site_content ""
import_site_content "--url=localhost:8888/site2"
import_site_content "--url=localhost:8888/site3"
import_site_content "--url=localhost:8888/site4"
import_site_content "--url=localhost:8888/site5"

echo "=== MilliCache afterStart complete ==="
