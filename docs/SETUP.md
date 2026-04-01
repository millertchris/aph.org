# Detailed Setup Guide

Complete guide for setting up the APH.org local development environment.

## System Requirements

### macOS
- Docker Desktop 4.x+ OR Colima
- Homebrew (recommended for installing DDEV)

### Windows
- Docker Desktop with WSL2 backend
- WSL2 with Ubuntu

### Linux
- Docker Engine
- docker-compose

## Installing DDEV

### macOS (Homebrew)
```bash
brew install ddev/ddev/ddev
```

### Windows (Chocolatey)
```powershell
choco install ddev
```

### Linux
```bash
curl -fsSL https://pkg.ddev.com/apt/gpg.key | gpg --dearmor | sudo tee /etc/apt/keyrings/ddev.gpg > /dev/null
echo "deb [signed-by=/etc/apt/keyrings/ddev.gpg] https://pkg.ddev.com/apt/ * *" | sudo tee /etc/apt/sources.list.d/ddev.list
sudo apt update && sudo apt install -y ddev
```

### Verify Installation
```bash
ddev version
```

## Step-by-Step Setup

### 1. Clone the Repository

```bash
git clone <repository-url> aph-ddev
cd aph-ddev
```

### 2. Start DDEV

```bash
ddev start
```

This will:
- Create Docker containers
- Install Composer dependencies
- Copy `.env.example` to `.env` (if not exists)

### 3. Configure Environment

Edit `.env` and update:

```bash
# Generate fresh salts at https://roots.io/salts.html
AUTH_KEY='your-unique-phrase'
SECURE_AUTH_KEY='your-unique-phrase'
# ... etc
```

### 4. Obtain Database Export

Export the production database using one of these methods:

#### Option A: phpMyAdmin
1. Log into hosting control panel
2. Open phpMyAdmin
3. Select the WordPress database
4. Export -> Quick -> SQL format
5. Download and save to `db/` directory

#### Option B: WP-CLI (if available on server)
```bash
wp db export --add-drop-table database.sql
```

#### Option C: Hosting Panel
Many hosts offer one-click database exports in their control panels.

### 5. Import Database

Place your SQL file in the `db/` directory, then:

```bash
ddev setup-db
```

The script will:
1. Drop existing tables
2. Import the SQL file
3. Run search-replace for URLs
4. Flush rewrite rules
5. Clear caches

### 6. Verify Installation

```bash
# Check site is accessible
ddev launch

# Verify WordPress
ddev wp option get siteurl
# Should return: https://aph.ddev.site

# Check plugins
ddev wp plugin list
```

## S3 Media Configuration

Media files are stored on S3 via WP Offload Media. To view production media locally:

### Read-Only Access (Recommended)
No additional configuration needed - media URLs point to S3 automatically.

### Upload Access (Optional)
If you need to upload media during development:

1. Create IAM credentials with S3 access
2. Add to `.env`:
```env
AS3CF_SETTINGS_BUCKET='production-bucket-name'
AS3CF_SETTINGS_REGION='us-east-1'
AWS_ACCESS_KEY_ID='your-key'
AWS_SECRET_ACCESS_KEY='your-secret'
AS3CF_COPY_TO_S3=true
```

**Warning**: Be careful not to overwrite production media!

## WP Migrate DB Pro Setup

For syncing database changes from production:

1. Add license to `.env`:
```env
WPMDB_LICENCE='your-license-key'
```

2. In WP Admin -> Tools -> Migrate DB Pro:
   - Set up connection to production
   - Configure pull settings
   - Exclude large/problematic tables if needed

### Recommended Table Exclusions
For large databases, consider excluding:
- `wp_actionscheduler_*` (Action Scheduler logs)
- `wp_statistics_*` (Analytics data)
- `wp_wc_*_log` (WooCommerce logs)

## Performance Optimization

### Increase Docker Resources
In Docker Desktop preferences:
- CPUs: 4+
- Memory: 8GB+
- Disk: 64GB+

## Common Issues

### "Error establishing database connection"
```bash
ddev restart
```

### Composer memory errors
```bash
ddev exec php -d memory_limit=-1 /usr/local/bin/composer install
```

### Blank page / white screen
Check logs:
```bash
ddev logs -f
cat web/app/debug.log
```

### SSL certificate errors
```bash
mkcert -install  # If not already done
ddev restart
```

### Port conflicts
```bash
ddev stop --all
ddev start
```

## Cleanup / Reset

### Reset database
```bash
ddev setup-db  # Re-import from SQL file
```

### Full reset
```bash
ddev delete --omit-snapshot
ddev start
```
