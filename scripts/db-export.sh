#!/bin/bash
#
# Database Export Script for APH WordPress
# Creates a lean database export for local development
#
# Usage: ./scripts/db-export.sh
# Output: exports/aph-db-YYYY-MM-DD.sql.gz
#

set -e

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
EXPORTS_DIR="$PROJECT_ROOT/exports"
DATE=$(date +%Y-%m-%d)
EXPORT_FILE="aph-db-${DATE}.sql"
EXPORT_PATH="$EXPORTS_DIR/$EXPORT_FILE"

# Tables to exclude (data only, structure is preserved separately)
# These contain analytics, metrics, and logs not needed for local development
EXCLUDE_TABLES=(
    # SearchWP metrics tables (~3.4GB)
    "wp_swpext_metrics_ids"
    "wp_swpext_metrics_searches"
    "wp_swpext_metrics_clicks"
    "wp_swpext_metrics_queries"
    "wp_searchwp_log"
    "wp_swp_log"

    # Log tables (~1.1GB)
    "wp_gravitysmtp_events"
    "wp_post_smtp_logs"
    "wp_defender_lockout_log"
    "wp_defender_lockout"
    "wp_defender_scan"
    "wp_defender_scan_item"
    "wp_defender_audit_log"
)

# Convert array to comma-separated string for wp-cli
EXCLUDE_TABLES_CSV=$(IFS=,; echo "${EXCLUDE_TABLES[*]}")

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}APH Database Export for Local Development${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Change to project root
cd "$PROJECT_ROOT"

# Verify wp-cli is available
if ! command -v wp &> /dev/null; then
    echo -e "${RED}Error: wp-cli is not installed or not in PATH${NC}"
    exit 1
fi

# Create exports directory if it doesn't exist
mkdir -p "$EXPORTS_DIR"

echo -e "${YELLOW}Step 1: Exporting database (excluding log/metrics tables)...${NC}"
echo "This may take several minutes for a large database."
echo ""
echo "Excluding tables: ${EXCLUDE_TABLES_CSV}"
echo ""

# Export database with single-transaction (configured in wp-cli.yml)
# --single-transaction ensures consistent export without locking tables
# --exclude_tables takes a comma-separated list
# --set-gtid-purged=OFF suppresses GTID warning for managed MySQL
wp db export "$EXPORT_PATH" \
    --exclude_tables="$EXCLUDE_TABLES_CSV" \
    --set-gtid-purged=OFF \
    2>&1

EXPORT_STATUS=$?

if [ $EXPORT_STATUS -ne 0 ] || [ ! -f "$EXPORT_PATH" ]; then
    echo -e "${RED}Error: Export failed (exit code: $EXPORT_STATUS)${NC}"
    exit 1
fi

# Get uncompressed size
UNCOMPRESSED_SIZE=$(du -h "$EXPORT_PATH" | cut -f1)
echo ""
echo -e "Uncompressed export size: ${GREEN}$UNCOMPRESSED_SIZE${NC}"

echo ""
echo -e "${YELLOW}Step 2: Compressing export file...${NC}"

# Compress with gzip (best compression)
gzip -f "$EXPORT_PATH"

COMPRESSED_PATH="${EXPORT_PATH}.gz"
COMPRESSED_SIZE=$(du -h "$COMPRESSED_PATH" | cut -f1)

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Export Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Output file: ${GREEN}$COMPRESSED_PATH${NC}"
echo -e "Compressed size: ${GREEN}$COMPRESSED_SIZE${NC}"
echo ""
echo "To use this export locally:"
echo "  1. Copy the file to your local machine"
echo "  2. Run: ddev import-db --file=exports/$EXPORT_FILE.gz"
echo "  3. Or use: ./scripts/db-import.sh exports/$EXPORT_FILE.gz"
echo ""
