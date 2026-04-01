#!/bin/bash

# ==============================================================================
# APH.org — Pull Database from Production via WP Migrate DB Pro CLI
# ==============================================================================
#
# Usage:
#   ./scripts/db-pull.sh                 # On SpinupWP server
#   ddev exec bash scripts/db-pull.sh    # On DDEV locally
#
# This script is safe to commit — it reads credentials from .env.
# Set WPMDB_SOURCE_URL and WPMDB_SOURCE_KEY in your .env file.
#
# ==============================================================================

# ---- Configuration ----

# Load .env if running outside of WordPress/DDEV context
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
if [ -f "$PROJECT_ROOT/.env" ]; then
  set -a
  source "$PROJECT_ROOT/.env"
  set +a
fi

# Source site URL (production)
# Set in .env: WPMDB_SOURCE_URL='https://www.aph.org'
SOURCE_URL="${WPMDB_SOURCE_URL:-}"

# Source site secret key (from production WP Admin → Tools → Migrate DB Pro → Settings)
# Set in .env: WPMDB_SOURCE_KEY='your-secret-key'
SOURCE_KEY="${WPMDB_SOURCE_KEY:-}"

# ---- Tables to exclude ----
# These are either rebuildable indexes, logs, analytics, or data not needed for dev.
# Total savings: ~1.7GB+ excluded from the ~5.5GB database.

EXCLUDE_TABLES=(
  # SearchWP index — 702MB, fully rebuildable (Tools → SearchWP → rebuild index)
  wp_swp_index
  wp_swp_cf
  wp_swp_tax
  wp_swp_terms
  wp_searchwp_index
  wp_searchwp_tokens

  # Yoast SEO — 58MB, rebuildable (SEO → Tools → reindex)
  wp_yoast_indexable
  wp_yoast_indexable_hierarchy
  wp_yoast_primary_term
  wp_yoast_seo_links
  wp_yoast_seo_meta

  # Broken Link Checker — 9MB, rescans automatically
  wp_blc_filters
  wp_blc_instances
  wp_blc_links
  wp_blc_synch

  # Gravity Forms entries — 84MB, usually not needed for dev
  wp_gf_entry
  wp_gf_entry_meta
  wp_gf_entry_notes
  wp_gf_form_view

  # Gravity SMTP logs — 4MB
  wp_gravitysmtp_event_logs

  # ActionScheduler — 6MB, regenerates on its own
  wp_actionscheduler_actions
  wp_actionscheduler_claims
  wp_actionscheduler_groups
  wp_actionscheduler_logs

  # WP All Export history — 31MB, not needed for dev
  wp_pmxe_exports
  wp_pmxe_posts
  wp_pmxe_templates

  # Defender antibot — 7MB, not needed locally (Defender is deactivated)
  wp_defender_antibot
  wp_defender_email_log
  wp_defender_audit_log
  wp_defender_lockout
  wp_defender_lockout_log
  wp_defender_scan
  wp_defender_scan_item

  # CartFlows abandonment tracking — 4MB
  wp_cartflows_ca_cart_abandonment
  wp_cartflows_ca_email_history

  # Redirection logs — runtime data
  wp_redirection_logs
  wp_redirection_404

  # Comments — 270MB, mostly spam. Include if you need them.
  # Uncomment the next line to exclude:
  # wp_comments
  # wp_commentmeta
)

# ==============================================================================
# Script execution — use with WP-CLI
# ==============================================================================

echo ""
echo "================================================"
echo " APH.org Database Pull (WP Migrate DB Pro CLI)"
echo "================================================"
echo ""
echo "Source:  ${SOURCE_URL:-NOT SET}"
echo "Exclude: ${#EXCLUDE_TABLES[@]} tables"
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

# Build exclude list
EXCLUDE_LIST=$(IFS=,; echo "${EXCLUDE_TABLES[*]}")

echo "Pulling database..."
echo "(This may take several minutes for large databases)"
echo ""

wp migratedb pull "$SOURCE_URL" "$SOURCE_KEY" \
  --skip-replace-guids \
  --exclude-spam \
  --exclude-tables="$EXCLUDE_LIST" \
  --skip-plugins --skip-themes

echo ""
echo "Pull complete. Running post-import tasks..."
echo ""

# Deactivate production-only plugins
wp plugin deactivate wp-defender --skip-plugins --skip-themes 2>/dev/null || true

# Flush caches
wp rewrite flush --skip-plugins --skip-themes 2>/dev/null || true
wp cache flush --skip-plugins --skip-themes 2>/dev/null || true
wp transient delete --all --skip-plugins --skip-themes 2>/dev/null || true

echo ""
echo "Done! Next steps:"
echo "  1. Reset your admin password:"
echo "     wp user update <username> --user_pass='password' --skip-plugins --skip-themes"
echo "  2. Rebuild SearchWP index: WP Admin → SearchWP → rebuild"
echo "  3. Rebuild Yoast index: WP Admin → SEO → Tools → reindex"
echo ""
