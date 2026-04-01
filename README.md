# APH.org WordPress Development Environment

Local WordPress development environment for [aph.org](https://www.aph.org) using DDEV and Bedrock.

## Prerequisites

- [DDEV](https://ddev.readthedocs.io/en/stable/) (v1.22+)
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) or Colima

## Quick Start

```bash
# 1. Clone the repository
git clone <repository-url> aph-ddev
cd aph-ddev

# 2. Start DDEV (installs Composer dependencies automatically)
ddev start

# 3. Configure environment (optional - edit salts, S3 settings)
# Edit .env with your settings

# 4. Import database (place SQL file in db/ directory first)
ddev setup-db

# 5. Visit the site
ddev launch
```

## URLs

- **Site**: https://aph.ddev.site
- **Admin**: https://aph.ddev.site/wp/wp-admin

## Project Structure

```
aph-ddev/
├── .ddev/                   # DDEV configuration
├── config/                  # Bedrock configuration
│   ├── application.php      # Main config
│   └── environments/        # Environment-specific configs
├── db/                      # Database import files (gitignored)
├── docs/                    # Documentation
├── scripts/                 # Helper scripts
├── vendor/                  # Composer dependencies (gitignored)
├── web/
│   ├── app/                 # WordPress content
│   │   ├── mu-plugins/      # Must-use plugins
│   │   ├── plugins/         # Plugins (git-tracked)
│   │   ├── themes/          # Themes (git-tracked)
│   │   └── uploads/         # Local uploads (gitignored)
│   ├── wp/                  # WordPress core (gitignored, Composer-managed)
│   ├── index.php            # Front controller
│   └── wp-config.php        # Bedrock bootstrap
├── .env.example             # Environment template
├── .gitignore
├── composer.json
└── README.md
```

## Database

### Initial Import

1. Obtain a database export from production (phpMyAdmin, WP-CLI, or hosting panel)
2. Place the `.sql` or `.sql.gz` file in the `db/` directory
3. Run: `ddev setup-db`

The script automatically:
- Imports the database
- Runs search-replace (`www.aph.org` -> `aph.ddev.site`)
- Deactivates production-only plugins (WPMU Defender — 2FA/reCAPTCHA breaks locally)
- Flushes rewrite rules
- Clears caches

After import, reset your admin password:
```bash
ddev wp user update <username> --user_pass='password' --skip-plugins --skip-themes
```

### Using WP Migrate DB Pro

For incremental syncs after initial setup:
1. Add your license to `.env`: `WPMDB_LICENCE='your-key'`
2. Configure connection in WP Admin -> Tools -> Migrate DB Pro

## Media / S3

Media files are served from the production S3 bucket via WP Offload Media.

- **Local uploads**: Disabled by default (stays local during dev)
- **Serving from S3**: Enabled (production media displays correctly)

To configure S3 access, update `.env`:
```env
AS3CF_SETTINGS_BUCKET='your-bucket'
AS3CF_SETTINGS_REGION='us-east-1'
```

## Common Commands

```bash
# Start/stop environment
ddev start
ddev stop

# WP-CLI
ddev wp plugin list
ddev wp user list
ddev wp cache flush

# Database
ddev setup-db              # Import and configure database
ddev mysql                 # MySQL CLI

# Composer
ddev composer install
ddev composer update

# Logs
ddev logs -f               # Follow logs
ddev describe              # Show project info

# SSH into container
ddev ssh
```

## Debugging

Debug mode is enabled by default in development. Logs are written to:
- `web/app/debug.log`

### Xdebug

```bash
ddev xdebug on
```

Configure your IDE to listen on port 9003.

## Theme Development

The active theme is `mightily` at `web/app/themes/mightily/`.

```bash
ddev ssh
cd web/app/themes/mightily
npm install    # If theme has build process
npm run dev
```

## Updating WordPress

WordPress core is managed via Composer:
```bash
ddev composer update roots/wordpress
```

## Architecture

This project uses **Bedrock** by Roots:
- Environment-based configuration via `.env`
- Improved security (wp-content outside webroot)
- Composer for WordPress core management
- Modern PHP practices

Plugins are manually installed (not Composer-managed) and tracked in git.

## Troubleshooting

See [docs/SETUP.md](docs/SETUP.md) for detailed setup instructions and common issues.
