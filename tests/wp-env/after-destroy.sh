#!/usr/bin/env bash
#
# wp-env afterDestroy lifecycle script for MilliCache.
# Tears down Redis / KeyDB / Dragonfly containers and volumes.
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "=== MilliCache afterDestroy ==="
docker compose -f "$SCRIPT_DIR/docker-compose.custom.yml" down -v 2>/dev/null || true
echo "=== MilliCache afterDestroy complete ==="
