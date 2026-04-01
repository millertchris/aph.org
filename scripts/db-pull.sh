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

# ---- Tables to exclude ----
# WP Migrate DB Pro uses --include-tables (whitelist), not exclude.
# We build the include list dynamically by querying all tables and filtering out
# the ones we don't need. This way new tables are automatically included.
#
# Tables we SKIP — saves ~1.3GB total:
#
# Rebuildable indexes (~780MB):
#   - wp_swp_*, wp_searchwp_*    (SearchWP — 702MB, rebuild via WP Admin)
#   - wp_yoast_*                 (Yoast SEO — 58MB, rebuild via SEO → Tools)
#   - wp_facetwp_index           (FacetWP — 9MB, rebuilds on reindex)
#   - wp_wps_*                   (SearchWP metrics — 12MB, rebuildable)
#
# WooCommerce analytics/lookups (~148MB):
#   - wp_wc_order_product_lookup (111MB, rebuild via WC → Status → Tools)
#   - wp_wc_order_stats          (29MB, rebuildable)
#   - wp_wc_customer_lookup      (7MB, rebuildable)
#   - wp_wc_download_log         (2MB, download tracking)
#
# WooCommerce order notes (~271MB):
#   - wp_comments/wp_commentmeta (271MB — 585K rows, ALL are WC order notes
#     like "Payment received", "Order shipped". Not user comments or reviews.
#     Useful for debugging but not essential for dev/staging.)
#
# Logs, sessions, runtime data (~60MB):
#   - wp_gf_entry*, wp_gf_form_view (Gravity Forms entries — 84MB)
#   - wp_gravitysmtp_*           (SMTP logs — 4MB)
#   - wp_actionscheduler_*       (ActionScheduler — 6MB, regenerates)
#   - wp_pmxe_*, wp_pmxi_*       (WP All Export/Import — 32MB)
#   - wp_defender_*              (Defender — 7MB, deactivated locally)
#   - wp_cartflows_*             (CartFlows — 4MB)
#   - wp_blc_*                   (Broken Link Checker — 9MB, rescans)
#   - wp_redirection_logs/404    (Redirection logs)
#   - wp_woocommerce_sessions    (Active sessions — meaningless on another server)
#   - wp_oauth_*                 (OAuth tokens — session-specific)
#   - wp_wsal_*                  (WP Security Audit Log)
#   - wp_wf*                     (Wordfence leftovers)

# ---- Build include list dynamically ----

build_include_tables() {
  # Get all tables from local DB, then filter out the ones we want to skip
  wp db tables --all-tables --format=csv 2>/dev/null | tr ',' '\n' | grep -v \
    -e 'wp_swp_' \
    -e 'wp_searchwp_' \
    -e 'wp_yoast_' \
    -e 'wp_facetwp_index' \
    -e 'wp_wps_' \
    -e 'wp_blc_' \
    -e 'wp_gf_entry' \
    -e 'wp_gf_form_view' \
    -e 'wp_gravitysmtp_' \
    -e 'wp_actionscheduler_' \
    -e 'wp_pmxe_' \
    -e 'wp_pmxi_' \
    -e 'wp_defender_' \
    -e 'wp_cartflows_' \
    -e 'wp_redirection_logs' \
    -e 'wp_redirection_404' \
    -e 'wp_wc_order_product_lookup' \
    -e 'wp_wc_order_stats' \
    -e 'wp_wc_customer_lookup' \
    -e 'wp_wc_download_log' \
    -e 'wp_woocommerce_sessions' \
    -e 'wp_comments' \
    -e 'wp_commentmeta' \
    -e 'wp_oauth_' \
    -e 'wp_wsal_' \
    -e 'wp_wf' \
    -e 'wp_smush_dir_images' \
    -e 'wp_post_smtp_logmeta' \
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

# Post types to exclude — bloat inside wp_posts/wp_postmeta:
#   - postman_sent_mail: 491MB — Post SMTP email logs with full transcripts
#   - wp-rest-api-log:    36MB — REST API request/response debug logs
EXCLUDE_POST_TYPES="postman_sent_mail,wp-rest-api-log"

wp migratedb pull "$SOURCE_URL" "$SOURCE_KEY" \
  --skip-replace-guids \
  --exclude-spam \
  --exclude-post-types="$EXCLUDE_POST_TYPES" \
  $TABLE_FLAG

echo ""
echo "Pull complete. Running post-import tasks..."
echo ""

# Deactivate production-only plugins
wp plugin deactivate wp-defender --skip-plugins --skip-themes 2>/dev/null && \
  echo "Deactivated: wp-defender" || echo "wp-defender already inactive"

# Clean up metadata bloat that can't be excluded by table/post-type
echo "Cleaning up metadata bloat..."
wp db query "DELETE FROM wp_postmeta WHERE meta_key = 'wp-smpro-smush-data'" --skip-plugins --skip-themes 2>/dev/null && \
  echo "  Removed Smush optimization data (~14MB)" || true
wp db query "DELETE FROM wp_postmeta WHERE meta_key LIKE '_oembed_%'" --skip-plugins --skip-themes 2>/dev/null && \
  echo "  Removed oEmbed cache entries" || true

# Flush caches
wp rewrite flush --skip-plugins --skip-themes 2>/dev/null || true
wp cache flush --skip-plugins --skip-themes 2>/dev/null || true
wp transient delete --all --skip-plugins --skip-themes 2>/dev/null || true

echo ""
echo "================================================"
echo " Database pull complete! $(date)"
echo "================================================"
echo ""
echo "Post-pull steps:"
echo "  1. Reset admin password:"
echo "     wp user update <username> --user_pass='password'"
echo ""
echo "Rebuild indexes (excluded from pull to save ~780MB):"
echo "  2. SearchWP:  WP Admin → SearchWP → Settings → rebuild index"
echo "  3. Yoast SEO: WP Admin → SEO → Tools → reindex"
echo "  4. FacetWP:   WP Admin → FacetWP → Settings → reindex"
echo "  5. WooCommerce lookups: WP Admin → WooCommerce → Status → Tools →"
echo "     'Regenerate order statistics' + 'Regenerate customer lookup tables'"
echo ""
