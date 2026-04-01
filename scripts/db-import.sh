#!/bin/bash
#
# Database Import Script for APH WordPress (Local DDEV)
# Imports production export and performs URL replacements
#
# Usage: ./scripts/db-import.sh exports/aph-db-YYYY-MM-DD.sql.gz
#

set -e

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
PRODUCTION_URL="https://www.aph.org"
LOCAL_URL="https://aph-local.ddev.site:8443"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}APH Database Import for Local Development${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check for DDEV
if ! command -v ddev &> /dev/null; then
    echo -e "${RED}Error: ddev is not installed or not in PATH${NC}"
    echo "This script is designed to run in a DDEV local environment."
    exit 1
fi

# Check for input file
if [ -z "$1" ]; then
    echo -e "${RED}Error: No import file specified${NC}"
    echo ""
    echo "Usage: $0 <path-to-sql-file>"
    echo ""
    echo "Example:"
    echo "  $0 exports/aph-db-2026-01-18.sql.gz"
    exit 1
fi

IMPORT_FILE="$1"

# Handle relative paths
if [[ ! "$IMPORT_FILE" = /* ]]; then
    IMPORT_FILE="$PROJECT_ROOT/$IMPORT_FILE"
fi

if [ ! -f "$IMPORT_FILE" ]; then
    echo -e "${RED}Error: File not found: $IMPORT_FILE${NC}"
    exit 1
fi

# Change to project root
cd "$PROJECT_ROOT"

# Verify we're in a DDEV project
if [ ! -f ".ddev/config.yaml" ]; then
    echo -e "${RED}Error: Not in a DDEV project directory${NC}"
    echo "Make sure you're running this from the project root."
    exit 1
fi

echo -e "${YELLOW}Step 1: Importing database...${NC}"
echo "File: $IMPORT_FILE"
echo ""

# Import the database using DDEV
ddev import-db --file="$IMPORT_FILE"

echo ""
echo -e "${YELLOW}Step 2: Replacing production URLs with local URLs...${NC}"
echo "  $PRODUCTION_URL -> $LOCAL_URL"
echo ""

# Run search-replace for main URL
ddev wp search-replace "$PRODUCTION_URL" "$LOCAL_URL" --all-tables --skip-columns=guid

echo ""
echo -e "${YELLOW}Step 3: Flushing caches and rewrite rules...${NC}"

# Flush rewrite rules
ddev wp rewrite flush

# Flush object cache if available
ddev wp cache flush 2>/dev/null || true

# Flush transients
ddev wp transient delete --all 2>/dev/null || true

echo ""
echo -e "${YELLOW}Step 4: Verifying import...${NC}"

# Check site URL
SITE_URL=$(ddev wp option get siteurl)
HOME_URL=$(ddev wp option get home)

echo "  Site URL: $SITE_URL"
echo "  Home URL: $HOME_URL"

if [[ "$SITE_URL" == *"aph-local.ddev.site"* ]]; then
    echo -e "  ${GREEN}URLs correctly updated!${NC}"
else
    echo -e "  ${RED}Warning: URLs may not have been updated correctly${NC}"
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Import Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "Your local site should now be accessible at:"
echo -e "  ${GREEN}$LOCAL_URL${NC}"
echo ""
echo "If you experience issues:"
echo "  1. Run: ddev restart"
echo "  2. Clear browser cache"
echo "  3. Check: ddev describe"
echo ""
