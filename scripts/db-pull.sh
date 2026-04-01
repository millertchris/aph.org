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
# What gets pulled (~1.7GB from a 5.5GB production database):
#   - All content: pages, posts, products, documents, people, ACF fields
#   - All users and user meta
#   - All site options and settings
#   - WooCommerce products, categories, attributes, tax rates, shipping zones
#   - Gravity Forms structure (forms, fields — not entries)
#   - Redirection rules, menus, widgets
#
# What gets excluded (~3.8GB):
#   - Orders and all order data (~2GB)
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

# Use 'wp migrate' (current command name)
MIGRATE_CMD="wp migrate"

# ---- Static table whitelist ----
# Only these tables get pulled. Everything else is excluded.
# To add a new table: append it to this list and commit.

INCLUDE_TABLES=$(cat <<'TABLES' | tr '\n' ',' | sed 's/,$//' | sed '/^$/d'
wp_ac_conditional_format
wp_ac_segments
wp_admin_columns
wp_as3cf_items
wp_gf_addon_feed
wp_gf_draft_submissions
wp_gf_form
wp_gf_form_meta
wp_gf_form_revisions
wp_gf_rest_api_keys
wp_links
wp_lmfwc_activations
wp_lmfwc_api_keys
wp_lmfwc_generators
wp_lmfwc_licenses
wp_lmfwc_licenses_meta
wp_options
wp_postmeta
wp_posts
wp_ppress_coupons
wp_ppress_customers
wp_ppress_forms
wp_ppress_formsmeta
wp_ppress_meta_data
wp_ppress_ordermeta
wp_ppress_orders
wp_ppress_plans
wp_ppress_sessions
wp_ppress_subscriptions
wp_redirection_groups
wp_redirection_items
wp_smartcrawl_redirects
wp_termmeta
wp_terms
wp_term_relationships
wp_term_taxonomy
wp_usermeta
wp_users
wp_wc_admin_notes
wp_wc_admin_note_actions
wp_wc_category_lookup
wp_wc_product_attributes_lookup
wp_wc_product_download_directories
wp_wc_product_meta_lookup
wp_wc_rate_limits
wp_wc_reserved_stock
wp_wc_tax_rate_classes
wp_wc_webhooks
wp_woocommerce_api_keys
wp_woocommerce_attribute_taxonomies
wp_woocommerce_exported_csv_items
wp_woocommerce_log
wp_woocommerce_payment_tokenmeta
wp_woocommerce_payment_tokens
wp_woocommerce_shipping_zones
wp_woocommerce_shipping_zone_locations
wp_woocommerce_shipping_zone_methods
wp_woocommerce_tax_rates
wp_woocommerce_tax_rate_locations
TABLES
)

# ---- Post types to exclude from wp_posts/wp_postmeta ----
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
  echo "  WPMDB_SOURCE_URL='https://www.aph.org/wp'"
  echo "  WPMDB_SOURCE_KEY='your-secret-key-from-production'"
  echo ""
  echo "Get the secret key from production WP Admin → Tools → Migrate DB Pro → Settings."
  exit 1
fi

echo "Including $(echo "$INCLUDE_TABLES" | tr ',' '\n' | wc -l | tr -d ' ') tables (excluding orders, indexes, logs)"
echo "Excluding post types: $EXCLUDE_POST_TYPES"
echo ""
echo "Pulling database (~1.7GB estimated)..."
echo "(This may take several minutes)"
echo ""

$MIGRATE_CMD pull "$SOURCE_URL" "$SOURCE_KEY" \
  --skip-replace-guids \
  --exclude-spam \
  --exclude-post-types="$EXCLUDE_POST_TYPES" \
  --include-tables="$INCLUDE_TABLES"

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
