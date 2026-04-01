---
name: db-workflow
description: Database workflow guide for APH.org — import, export, search-replace, snapshots, and troubleshooting. Use when working with the APH.org database.
---

# APH.org Database Workflow

## Quick Reference

```bash
ddev setup-db                    # Import from db/ with search-replace
ddev snapshot                    # Save current DB state
ddev snapshot restore            # Restore last snapshot
ddev snapshot --name=before-fix  # Named snapshot
ddev mysql                       # MySQL CLI
```

## Importing a Fresh Database

### 1. Get the SQL dump
Obtain from the team (S3, Google Drive, etc.). Can be `.sql` or `.sql.gz`.

### 2. Place in db/ directory
```bash
cp ~/Downloads/aph-db-export.sql.gz db/
```

### 3. Run setup-db
```bash
ddev setup-db
```

This command automatically:
- Drops and recreates the database
- Imports the SQL file (decompresses .gz)
- Search-replaces URLs: `www.aph.org` → `aph.ddev.site`
- Deactivates WPMU Defender (2FA/reCAPTCHA breaks locally)
- Flushes rewrite rules and caches

### 4. Reset admin password
```bash
ddev wp user update support@prolificdigital.com --user_pass='password' --skip-plugins --skip-themes
```

## Exporting the Database

```bash
# Quick export
ddev wp db export db/local-export.sql --skip-plugins --skip-themes

# Compressed export
ddev wp db export - --skip-plugins --skip-themes | gzip > db/local-export.sql.gz

# Export excluding large tables (saves space)
ddev wp db export db/local-export.sql --skip-plugins --skip-themes \
  --tables=$(ddev mysql -N -e "SELECT GROUP_CONCAT(table_name) FROM information_schema.tables WHERE table_schema='db' AND table_name NOT LIKE 'wp_actionscheduler%' AND table_name NOT LIKE 'wp_statistics%' AND table_name NOT LIKE 'wp_blc_%' AND table_name NOT LIKE 'wp_redirection_logs'")
```

## Snapshots (Quick Save/Restore)

Snapshots are faster than full imports — use them before risky changes:

```bash
# Save
ddev snapshot --name=before-plugin-update

# Restore
ddev snapshot restore --latest
# or
ddev snapshot restore --name=before-plugin-update

# List snapshots
ddev snapshot --list

# Clean up old snapshots
ddev snapshot --cleanup
```

## Search-Replace

```bash
# Dry run (preview changes)
ddev wp search-replace 'old-value' 'new-value' --dry-run --all-tables --skip-plugins --skip-themes

# Execute
ddev wp search-replace 'old-value' 'new-value' --all-tables --skip-columns=guid --skip-plugins --skip-themes
```

Common replacements:
```bash
# Production → local
ddev wp search-replace 'https://www.aph.org' 'https://aph.ddev.site' --all-tables --skip-columns=guid --skip-plugins --skip-themes

# Fix mixed content
ddev wp search-replace 'http://aph.ddev.site' 'https://aph.ddev.site' --all-tables --skip-columns=guid --skip-plugins --skip-themes
```

## WP Migrate DB Pro (Incremental Sync)

For pulling just the changed data from production:

1. Set license in `.env`: `WPMDB_LICENCE='your-key'`
2. In WP Admin → Tools → Migrate DB Pro
3. Configure connection to production
4. Pull with table exclusions:
   - `wp_actionscheduler_*`
   - `wp_statistics_*`
   - `wp_blc_*`
   - `wp_redirection_logs`

## Troubleshooting

### "Error establishing database connection"
```bash
ddev restart
# Check .env has: DB_NAME=db, DB_USER=db, DB_PASSWORD=db, DB_HOST=db
```

### Import runs out of memory
```bash
# For very large files, import directly via mysql
ddev mysql < db/large-file.sql
# Then run search-replace manually
```

### WP-CLI crashes during search-replace
Use `--skip-plugins --skip-themes` to avoid plugin compatibility issues:
```bash
ddev wp search-replace 'old' 'new' --all-tables --skip-plugins --skip-themes
```

### Need to start completely fresh
```bash
ddev delete --omit-snapshot
ddev start
ddev setup-db
```
