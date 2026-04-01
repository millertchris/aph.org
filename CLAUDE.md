# CLAUDE.md

This file provides guidance to Claude Code when working with code in this repository.

## Project Overview

APH.org is a WordPress e-commerce site using the **Bedrock** framework with **WooCommerce**. Local development uses **DDEV** for containerized development.

| Setting | Value |
|---------|-------|
| Site URL | https://aph.ddev.site |
| Admin URL | https://aph.ddev.site/wp/wp-admin |
| PHP Version | 8.4 |
| Database | MariaDB 10.11 |
| Framework | Bedrock by Roots |

## Common Commands

```bash
# Start/stop environment
ddev start
ddev stop
ddev restart

# WP-CLI
ddev wp plugin list
ddev wp cache flush
ddev wp rewrite flush
ddev wp transient delete --all

# Database operations
ddev setup-db              # Import from db/ directory with search-replace
ddev mysql                 # MySQL CLI
ddev snapshot              # Create database snapshot
ddev snapshot restore      # Restore snapshot

# Debugging
ddev logs -f               # Follow container logs
ddev xdebug on             # Enable Xdebug (port 9003)
```

Debug log location: `web/app/debug.log`

## Custom Agents

Specialized sub-agents in `.claude/agents/` — invoke by name for focused tasks:

| Agent | Model | Use For |
|-------|-------|---------|
| `wp-developer` | sonnet | Theme mods, WooCommerce hooks, APH PHP classes, CPTs, REST APIs |
| `wp-debugger` | sonnet | PHP errors, plugin conflicts, Bedrock config issues, database problems |
| `qa-tester` | sonnet | Playwright testing — pages, WooCommerce flows, forms, accessibility |
| `security-auditor` | opus | Security audit — XSS, SQLi, CSRF, exposed secrets, OWASP top 10 |
| `performance-optimizer` | sonnet | Slow queries, asset optimization, DB bloat, caching opportunities |
| `plugin-developer` | sonnet | Scaffold and develop custom plugins following APH patterns |
| `frontend-developer` | sonnet | SCSS/JS/Gulp, responsive design, WCAG accessibility (top priority for APH) |

## Custom Skills

Slash commands in `.claude/skills/` — load project context on demand:

| Skill | Purpose |
|-------|---------|
| `/aph-dev` | Full project context — environment, structure, agents, key files, commands |
| `/db-workflow` | Database import/export/snapshot/search-replace workflow guide |

## Project Structure (Bedrock)

```
aph-ddev/
├── .ddev/
│   ├── config.yaml              # DDEV container configuration
│   └── commands/web/setup-db    # Custom DB import command
├── config/
│   ├── application.php          # Main WordPress config (reads from .env)
│   └── environments/            # Environment-specific overrides
│       ├── development.php      # Debug enabled, file mods allowed
│       ├── staging.php          # Indexing disabled
│       └── production.php       # Debug disabled, file mods disabled
├── db/                          # Database import files (gitignored)
│   └── .gitkeep
├── docs/
│   └── SETUP.md                 # Detailed setup guide
├── scripts/                     # Helper scripts (db-import, db-export)
├── vendor/                      # Composer dependencies (gitignored)
├── web/
│   ├── app/                     # WordPress content directory
│   │   ├── mu-plugins/          # Must-use plugins (git-tracked)
│   │   ├── plugins/             # Plugins (git-tracked, ~77 plugins)
│   │   ├── themes/mightily/     # Active custom theme (git-tracked)
│   │   └── uploads/             # Local uploads (gitignored, S3 in prod)
│   ├── wp/                      # WordPress core (gitignored, Composer-managed)
│   ├── index.php                # Front controller
│   └── wp-config.php            # Bedrock bootstrap loader
├── .env                         # Environment variables — NEVER COMMIT
├── .env.example                 # Documents all env vars with placeholders
├── .gitignore
├── composer.json                # Defines WP core + Bedrock dependencies
├── composer.lock                # Locks exact dependency versions
├─��� wp-cli.yml                   # WP-CLI path config for Bedrock
├── CLAUDE.md                    # This file
└── README.md                    # Developer onboarding guide
```

## Version Control Decisions

These decisions follow Bedrock conventions with one deliberate deviation for plugins.

### What is committed to git and why

| Path | Committed? | Reason |
|------|-----------|--------|
| `config/application.php` | Yes | Configuration logic — reads values from `.env`, contains no secrets itself. This is standard Bedrock practice. |
| `config/environments/*.php` | Yes | Environment overrides (debug settings, file mod permissions). No secrets. |
| `web/app/plugins/` | Yes | **Deviation from Bedrock default.** Bedrock normally gitignores plugins and manages them via Composer. We track them because many are premium (ACF Pro, Gravity Forms, SearchWP, FacetWP, WPMU DEV suite) requiring paid license auth for Composer downloads. Tracking in git means developers can clone and work without needing every license key in an `auth.json`. |
| `web/app/themes/` | Yes | Custom theme code — this is your application code. |
| `web/app/mu-plugins/` | Yes | Must-use plugins including Bedrock autoloader. |
| `composer.json` + `composer.lock` | Yes | Defines and locks dependency versions. `composer.lock` ensures every developer gets the exact same WordPress core version. |
| `wp-cli.yml` | Yes | Tells WP-CLI where WordPress core lives in Bedrock's non-standard structure (`web/wp`). Without it, every `ddev wp` command would need `--path=web/wp`. Bedrock only gitignores `wp-cli.local.yml` (personal overrides). |
| `.ddev/config.yaml` | Yes | Shared DDEV configuration so all developers use the same PHP version, database, etc. |
| `.ddev/commands/web/setup-db` | Yes | Custom DDEV command for database import with search-replace. Not a native DDEV feature — wraps import + URL replacement + cache flush into one command. |
| `.env.example` | Yes | Documents every environment variable with placeholder values. |
| `README.md`, `CLAUDE.md`, `docs/` | Yes | Documentation. |

### What is NOT committed to git and why

| Path | Reason |
|------|--------|
| `web/wp/` | WordPress core. Managed by Composer, installed via `composer install`. ~1,500 files / ~50MB of code you never modify. Every WP update would pollute git history with thousands of irrelevant file changes. `composer.lock` guarantees version consistency. |
| `vendor/` | PHP dependencies (dotenv, Bedrock framework, etc.). Same logic as WP core — reproducible from `composer.lock`, never modified by developers. |
| `.env` | **Contains secrets**: database credentials, AWS keys, API keys, license keys. Once a secret is in git history, it's there permanently even if deleted later. |
| `auth.json` | Composer authentication tokens for premium repositories. Contains license keys. |
| `db/*.sql`, `db/*.sql.gz` | Database dumps. Too large for git (342MB+ compressed, ~7GB uncompressed). Shared via S3, Google Drive, or team channels. |
| `web/app/uploads/` | Media files. Production media is served from S3 via WP Offload Media. Local uploads are developer-specific. |
| `*.log`, `web/app/wphb-cache/`, etc. | Runtime artifacts — logs, cache files, upgrade temp files. |
| `.ddev/*.local.yaml` | Developer-specific DDEV overrides. |

## Secrets and Environment Configuration

### How it works

Secrets are managed through a three-layer system:

1. **`.env`** (gitignored) — stores raw key-value pairs:
   ```
   FACETWP_LICENSE_KEY='e388717d0ceefc5114216a7a9e58b581'
   AWS_ACCESS_KEY_ID='AKIA...'
   ```

2. **`config/application.php`** (committed) — reads `.env` values and creates PHP constants:
   ```php
   if (env('FACETWP_LICENSE_KEY')) {
       define('FACETWP_LICENSE_KEY', env('FACETWP_LICENSE_KEY'));
   }
   ```

3. **WordPress plugins** — check for the constant as usual:
   ```php
   if (defined('FACETWP_LICENSE_KEY')) {
       $key = FACETWP_LICENSE_KEY; // Works exactly the same
   }
   ```

The `.env` file cannot run PHP — it's plain text. All PHP logic (conditionals, `serialize()`, arrays) lives in `application.php`. The `.env` only supplies values.

### Example: simple constant

```
# .env
ACF_PRO_LICENSE='b3JkZXJfaWQ9OTQ3ODV8dHlwZT1kZXZlbG9wZXJ8...'
```
```php
// config/application.php
if (env('ACF_PRO_LICENSE')) {
    define('ACF_PRO_LICENSE', env('ACF_PRO_LICENSE'));
}
```

### Example: complex configuration (S3 with serialize + array)

```
# .env
AS3CF_SETTINGS_BUCKET='aph-media'
AS3CF_SETTINGS_REGION='us-east-1'
AWS_ACCESS_KEY_ID='AKIA...'
AWS_SECRET_ACCESS_KEY='T9kz...'
AS3CF_DELIVERY_DOMAIN='media.aph.org'
```
```php
// config/application.php
if (env('AS3CF_SETTINGS_BUCKET')) {
    define('AS3CF_SETTINGS', serialize([
        'provider' => env('AS3CF_SETTINGS_PROVIDER') ?: 'aws',
        'access-key-id' => env('AWS_ACCESS_KEY_ID') ?: '',
        'secret-access-key' => env('AWS_SECRET_ACCESS_KEY') ?: '',
        'bucket' => env('AS3CF_SETTINGS_BUCKET'),
        'serve-from-s3' => true,
        'delivery-domain' => env('AS3CF_DELIVERY_DOMAIN') ?: '',
    ]));
}
```

### All environment variables

See `.env.example` for the complete list. Key categories:
- **Database**: `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST` (DDEV provides these automatically)
- **URLs**: `WP_HOME`, `WP_SITEURL`
- **Environment**: `WP_ENV`, `WP_DEBUG`
- **Auth salts**: `AUTH_KEY`, `SECURE_AUTH_KEY`, etc. (generate at https://roots.io/salts.html)
- **S3/Media**: `AS3CF_SETTINGS_BUCKET`, `AWS_ACCESS_KEY_ID`, etc.
- **License keys**: `FACETWP_LICENSE_KEY`, `ACF_PRO_LICENSE`, `WPMDB_LICENCE`, etc.
- **API credentials**: `FQ_URL_PRD`, `HUMANWARE_API_SECRET`, etc.

### Sharing secrets with the team

Never share secrets via git. Use:
- 1Password / LastPass / team password vault
- Encrypted team channel (Slack DM, etc.)
- Shared `.env` file on a private S3 bucket or Google Drive

## Database Workflow

### setup-db (custom DDEV command)

`ddev setup-db` is NOT a native DDEV command. It's a custom script at `.ddev/commands/web/setup-db` that wraps several steps:

1. Finds `.sql` or `.sql.gz` file in `db/` directory
2. Drops and recreates the database
3. Imports the SQL dump (auto-decompresses `.gz`)
4. Runs search-replace: `www.aph.org` / `aph.org` / `staging.aph.org` -> `aph.ddev.site`
5. Flushes rewrite rules
6. Clears object cache and transients

Native DDEV provides `ddev import-db` for basic imports, but it doesn't handle the search-replace or cleanup steps.

### Developer workflow

1. Get a SQL dump from the team (S3, Google Drive, etc.)
2. Place it in `db/` (this directory is gitignored)
3. Run `ddev setup-db`
4. Reset your admin password: `ddev wp user update <username> --user_pass='password' --skip-plugins --skip-themes`
5. Site is ready at https://aph.ddev.site
6. Admin at https://aph.ddev.site/wp/wp-admin

### Plugins auto-deactivated on import

The `setup-db` script automatically deactivates these production-only plugins after importing:

- **WPMU Defender** (`wp-defender`) — its 2FA, reCAPTCHA, login masking (`/site-manager`), and brute force protection all break local development. reCAPTCHA keys are registered to `www.aph.org` and won't work on `aph.ddev.site`. 2FA would require the production user's authenticator device.

If you need to test Defender locally, re-activate it manually: `ddev wp plugin activate wp-defender`

## Theme Architecture (Mightily)

The active theme uses a modular function architecture:

```
web/app/themes/mightily/
├── functions.php              # Main loader (requires modular files)
├── functions/
│   ├── helpers.php            # Utility functions
│   ├── wp/                    # WordPress customizations
│   ├── wp-hooks.php           # WordPress action/filter hooks
│   ├── wc/                    # WooCommerce customizations
│   ├── wc-hooks.php           # WooCommerce hooks
│   └── cpt/                   # Custom post types
├── classes/                   # PHP classes with autoloader
├── template-parts/            # Reusable template components
├── templates/                 # Page templates
└── assets/                    # Frontend assets
```

## WooCommerce Development

The site has extensive WooCommerce customizations. Always check WooCommerce is active:

```php
if (class_exists('WooCommerce')) {
    // WooCommerce code
}
```

WooCommerce hooks are centralized in `functions/wc-hooks.php` with implementations in `functions/wc/`.

## Plugins

Plugins are manually installed in `web/app/plugins/` and tracked in git. Major plugins include:
- WooCommerce (with extensions: subscriptions, payment gateways, shipping)
- Advanced Custom Fields Pro
- FacetWP (filtering)
- SearchWP (search)
- Gravity Forms
- WP Migrate DB Pro
- WP Offload Media (S3)
- WPMU DEV suite (Defender, Smush, Hummingbird)

To update a plugin: download the new version, replace files in `web/app/plugins/<plugin-name>/`, commit.

## Development Workflow

1. Make changes in `web/app/themes/mightily/` or `web/app/plugins/`
2. Clear caches: `ddev wp cache flush`
3. Check `web/app/debug.log` for PHP errors
4. Commit your changes to git

## Important Notes

- Media files are served from production S3 via WP Offload Media
- Database imports use `ddev setup-db` which auto-runs search-replace for URLs
- WordPress core (`web/wp/`) is read-only; update via `ddev composer update roots/wordpress`
- Never commit `.env` (contains secrets) or `web/app/uploads/` (local media)
- When running WP-CLI with plugin compatibility issues, use `--skip-plugins --skip-themes`
