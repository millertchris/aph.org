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
# What gets pulled (~1.7GB from a 5.5GB production database):
#   - All content: pages, posts, products, documents, people, ACF fields
#   - All users and user meta
#   - All site options and settings
#   - WooCommerce products, categories, attributes, tax rates, shipping zones
#   - Gravity Forms structure (forms, fields — not entries)
#   - Redirection rules, menus, widgets
#
# What gets excluded (~3.8GB):
#   - Orders and all order data (~2GB) — not needed for theme/plugin development
#   - Search indexes (~780MB) — SearchWP, Yoast, FacetWP (all rebuildable)
#   - Email logs (~491MB) — Post SMTP transcripts
#   - WC order notes (~271MB) — comments table is all order notes
#   - WC analytics lookups (~148MB) — rebuildable
#   - Form entries (~84MB) — Gravity Forms submissions
#   - Various logs and runtime data (~60MB)
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
# We build the include list dynamically by querying all tables and filtering out
# the ones below. New tables are automatically included.

build_include_tables() {
  wp db tables --all-tables --format=csv 2>/dev/null | tr ',' '\n' | grep -v \
    \
    -e 'wp_wc_orders$' \
    -e 'wp_wc_orders_meta' \
    -e 'wp_wc_order_addresses' \
    -e 'wp_wc_order_operational_data' \
    -e 'wp_woocommerce_order_items' \
    -e 'wp_woocommerce_order_itemmeta' \
    -e 'wp_woocommerce_downloadable_product_permissions' \
    -e 'wp_wc_order_product_lookup' \
    -e 'wp_wc_order_stats' \
    -e 'wp_wc_order_tax_lookup' \
    -e 'wp_wc_order_coupon_lookup' \
    -e 'wp_wc_customer_lookup' \
    -e 'wp_wc_download_log' \
    \
    -e 'wp_comments' \
    -e 'wp_commentmeta' \
    \
    -e 'wp_swp_' \
    -e 'wp_searchwp_' \
    -e 'wp_yoast_' \
    -e 'wp_facetwp_index' \
    -e 'wp_wps_' \
    \
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
    -e 'wp_woocommerce_sessions' \
    -e 'wp_oauth_' \
    -e 'wp_wsal_' \
    -e 'wp_wf' \
    -e 'wp_smush_dir_images' \
    -e 'wp_post_smtp_logmeta' \
    | tr '\n' ',' | sed 's/,$//'
}

# ---- Post types to exclude ----
# These are excluded from wp_posts and wp_postmeta via --exclude-post-types.
EXCLUDE_POST_TYPES="shop_order,shop_order_placehold,order_shipment,postman_sent_mail,wp-rest-api-log"

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

echo "Building table include list..."
INCLUDE_TABLES=$(build_include_tables)

if [ -z "$INCLUDE_TABLES" ]; then
  echo "WARNING: Could not build table list. Pulling all tables."
  TABLE_FLAG=""
else
  TABLE_FLAG="--include-tables=$INCLUDE_TABLES"
fi

echo "Excluding post types: $EXCLUDE_POST_TYPES"
echo ""
echo "Pulling database (~1.7GB estimated)..."
echo "(This may take several minutes)"
echo ""

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
echo "Estimated size: ~1.7GB (down from 5.5GB production)"
echo ""
echo "What was excluded:"
echo "  - All orders and order data (~2GB)"
echo "  - Search indexes, Yoast, FacetWP (~780MB)"
echo "  - Email logs, form entries, analytics (~900MB)"
echo ""
echo "Post-pull steps:"
echo "  1. Reset admin password:"
echo "     wp user update <username> --user_pass='password'"
echo ""
echo "  2. Rebuild indexes:"
echo "     - SearchWP:    WP Admin → SearchWP → Settings → rebuild index"
echo "     - Yoast SEO:   WP Admin → SEO → Tools → reindex"
echo "     - FacetWP:     WP Admin → FacetWP → Settings → reindex"
echo ""
echo "  Note: Orders are excluded. If you need specific orders for testing,"
echo "  pull them manually via WP Admin → Tools → Migrate DB Pro."
echo ""
