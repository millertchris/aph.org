#!/bin/bash

# ==============================================================================
# APH.org — Pull Database from Production via WP Migrate DB Pro CLI
# ==============================================================================
#
# Usage:
#   ./scripts/db-pull.sh                 # On SpinupWP server
#   ddev exec bash scripts/db-pull.sh    # On DDEV locally
#
# Scheduling (staging server cron — Sundays at 3am):
#   0 3 * * 0 cd /path/to/site && ./scripts/db-pull.sh >> /var/log/db-pull.log 2>&1
#
# First-time setup:
#   1. Ensure .env has WPMDB_SOURCE_URL and WPMDB_SOURCE_KEY
#   2. Ensure WP Migrate DB Pro is active on both source and destination
#   3. Run: ./scripts/db-pull.sh
#
# This script is safe to commit — it reads credentials from .env.
#
# ==============================================================================

set -e

# ---- Load .env ----

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
if [ -f "$PROJECT_ROOT/.env" ]; then
  set -a
  source "$PROJECT_ROOT/.env"
  set +a
fi

cd "$PROJECT_ROOT"

# Source site URL (production)
SOURCE_URL="${WPMDB_SOURCE_URL:-}"

# Source site secret key (from production WP Admin → Tools → Migrate DB Pro → Settings)
SOURCE_KEY="${WPMDB_SOURCE_KEY:-}"

# ---- Tables to include ----
# WP Migrate DB Pro uses --include-tables (whitelist), not exclude.
# Omitting --include-tables migrates ALL tables.
# To skip large rebuildable/unnecessary tables, we explicitly list what to include.
#
# Tables we SKIP (not listed below) — saves ~1.7GB:
#   - wp_swp_*, wp_searchwp_*    (SearchWP index — 702MB, rebuildable)
#   - wp_yoast_*                 (Yoast index — 58MB, rebuildable)
#   - wp_blc_*                   (Broken Link Checker — 9MB, rescans automatically)
#   - wp_gf_entry*, wp_gf_form_view (Gravity Forms entries — 84MB)
#   - wp_gravitysmtp_*           (SMTP logs — 4MB)
#   - wp_actionscheduler_*       (ActionScheduler — 6MB, regenerates)
#   - wp_pmxe_*                  (WP All Export — 31MB)
#   - wp_defender_*              (Defender — 7MB, deactivated locally)
#   - wp_cartflows_*             (CartFlows — 4MB)
#   - wp_redirection_logs/404    (Redirection logs)

# ---- Build include list dynamically ----
# Query the SOURCE database for all tables, then exclude the ones we don't want.
# This way new tables are automatically included.

build_include_tables() {
  # Get all tables from local DB, then filter out the ones we want to skip
  wp db tables --all-tables --format=csv 2>/dev/null | tr ',' '\n' | grep -v \
    -e 'wp_swp_' \
    -e 'wp_searchwp_' \
    -e 'wp_yoast_' \
    -e 'wp_blc_' \
    -e 'wp_gf_entry' \
    -e 'wp_gf_form_view' \
    -e 'wp_gravitysmtp_' \
    -e 'wp_actionscheduler_' \
    -e 'wp_pmxe_' \
    -e 'wp_defender_' \
    -e 'wp_cartflows_' \
    -e 'wp_redirection_logs' \
    -e 'wp_redirection_404' \
    | tr '\n' ',' | sed 's/,$//'
}

# ==============================================================================

echo ""
echo "================================================"
echo " APH.org Database Pull (WP Migrate DB Pro CLI)"
echo "================================================"
echo ""
echo "Source:    ${SOURCE_URL:-NOT SET}"
echo "Timestamp: $(date)"
echo ""

if [ -z "$SOURCE_URL" ] || [ -z "$SOURCE_KEY" ]; then
  echo "ERROR: Set WPMDB_SOURCE_URL and WPMDB_SOURCE_KEY in your .env file."
  echo ""
  echo "  WPMDB_SOURCE_URL='https://www.aph.org'"
  echo "  WPMDB_SOURCE_KEY='your-secret-key-from-production'"
  echo ""
  echo "Get the secret key from production WP Admin → Tools → Migrate DB Pro → Settings."
  exit 1
fi

# Check if WP Migrate DB Pro CLI is available
if ! wp migratedb --help &>/dev/null; then
  echo "ERROR: WP Migrate DB Pro CLI is not available."
  echo "Ensure the plugin is active: wp plugin activate wp-migrate-db-pro"
  exit 1
fi

echo "Building table include list (excluding rebuildable indexes, logs, analytics)..."
INCLUDE_TABLES=$(build_include_tables)

if [ -z "$INCLUDE_TABLES" ]; then
  echo "WARNING: Could not build table list. Pulling all tables."
  TABLE_FLAG=""
else
  TABLE_FLAG="--include-tables=$INCLUDE_TABLES"
fi

echo "Pulling database..."
echo "(This may take several minutes for large databases)"
echo ""

wp migratedb pull "$SOURCE_URL" "$SOURCE_KEY" \
  --skip-replace-guids \
  --exclude-spam \
  $TABLE_FLAG

echo ""
echo "Pull complete. Running post-import tasks..."
echo ""

# Deactivate production-only plugins
wp plugin deactivate wp-defender --skip-plugins --skip-themes 2>/dev/null && \
  echo "Deactivated: wp-defender" || echo "wp-defender already inactive"

# Flush caches
wp rewrite flush --skip-plugins --skip-themes 2>/dev/null || true
wp cache flush --skip-plugins --skip-themes 2>/dev/null || true
wp transient delete --all --skip-plugins --skip-themes 2>/dev/null || true

echo ""
echo "================================================"
echo " Database pull complete! $(date)"
echo "================================================"
echo ""
echo "Post-pull steps (if first-time setup):"
echo "  1. Reset admin password:"
echo "     wp user update <username> --user_pass='password'"
echo "  2. Rebuild SearchWP index: WP Admin → SearchWP → rebuild"
echo "  3. Rebuild Yoast index: WP Admin → SEO → Tools → reindex"
echo ""
