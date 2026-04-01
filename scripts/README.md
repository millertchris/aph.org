# APH Database Export/Import Scripts

Scripts for exporting the production database and importing it into local DDEV development environments.

## Overview

The production database is ~7GB, but much of this is analytics, metrics, and log data not needed for local development. These scripts create a lean export (~1-2GB compressed) by excluding unnecessary tables.

## Excluded Tables

The export excludes data (but keeps structure) from these tables:

| Category | Tables | Savings |
|----------|--------|---------|
| SearchWP Metrics | `wp_swpext_metrics_*`, `wp_searchwp_log`, `wp_swp_log` | ~3.4GB |
| Logs | `wp_gravitysmtp_events`, `wp_post_smtp_logs`, `wp_defender_*` | ~1.1GB |

## Usage

### On Production Server

```bash
cd /sites/www.aph.org/files
./scripts/db-export.sh
```

This creates: `exports/aph-db-YYYY-MM-DD.sql.gz`

### On Local DDEV Environment

**Option 1: Using DDEV directly**
```bash
# Copy the export file to your local project, then:
ddev import-db --file=exports/aph-db-YYYY-MM-DD.sql.gz

# Manually run search-replace
ddev wp search-replace 'https://www.aph.org' 'https://aph-local.ddev.site:8443' --all-tables --skip-columns=guid

# Flush caches
ddev wp rewrite flush
ddev wp cache flush
```

**Option 2: Using the import script (recommended)**
```bash
./scripts/db-import.sh exports/aph-db-YYYY-MM-DD.sql.gz
```

The import script automatically:
- Imports the database via DDEV
- Replaces production URLs with local URLs
- Flushes caches and rewrite rules
- Verifies the import was successful

## Expected File Sizes

| Stage | Size |
|-------|------|
| Full production DB | ~7GB |
| After excluding logs | ~2.5GB |
| Compressed (.gz) | ~700MB-1.5GB |

## Troubleshooting

### "wp-cli not found" error
Ensure wp-cli is installed and in your PATH:
```bash
wp --version
```

### "Not in a DDEV project" error
Run the import script from the project root where `.ddev/config.yaml` exists.

### Site shows production URLs after import
Run the search-replace manually:
```bash
ddev wp search-replace 'https://www.aph.org' 'https://aph-local.ddev.site:8443' --all-tables
```

### Database import is slow
Large imports can take several minutes. The compressed file is decompressed on-the-fly during import.

## Local Environment Details

- **Local URL**: `https://aph-local.ddev.site:8443`
- **Framework**: Bedrock WordPress
- **Database**: MySQL (via DDEV)

## Notes

- Exports are gitignored - don't commit database dumps
- The `--single-transaction` flag ensures consistent exports without locking tables
- WooCommerce orders are included in full (filtering by date is complex due to referential integrity)
