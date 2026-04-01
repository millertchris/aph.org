---
name: aph-dev
description: APH.org development context — environment details, project structure, available agents, common commands. Use when starting work on APH.org or needing project context.
---

# APH.org Development Context

## Environment

| Setting | Value |
|---------|-------|
| Site URL | https://aph.ddev.site |
| Admin URL | https://aph.ddev.site/wp/wp-admin |
| PHP | 8.4 |
| Database | MariaDB 10.11 |
| Framework | Bedrock by Roots |
| Theme | Mightily (`web/app/themes/mightily/`) |
| Build Tool | Gulp 4 (SCSS/JS) |
| Media | S3 via WP Offload Media (media.aph.org) |

## Project Structure

```
aph.org/
├── config/
│   ├── application.php          # Main config (reads .env, defines constants)
│   └── environments/
│       ├── development.php      # Debug on, file mods allowed
│       ├── staging.php
│       └── production.php
├── web/
│   ├── app/
│   │   ├── plugins/             # 77 plugins (git-tracked)
│   │   ├── themes/mightily/     # Custom theme
│   │   ├── mu-plugins/          # Must-use plugins
│   │   └── uploads/             # Local uploads (gitignored)
│   ├── wp/                      # WP core (Composer, gitignored)
│   └── wp-config.php            # Bedrock bootstrap
├── .env                         # Secrets (gitignored)
├── .env.example                 # Env var documentation
└── composer.json                # WP core + Bedrock deps
```

## Available Agents

| Agent | Use For |
|-------|---------|
| `wp-developer` | Theme mods, WooCommerce hooks, PHP classes, CPTs, REST APIs |
| `wp-debugger` | PHP errors, plugin conflicts, white screens, database issues |
| `qa-tester` | Playwright testing — pages, forms, WooCommerce flows, a11y |
| `security-auditor` | Security review — XSS, SQLi, CSRF, exposed secrets, OWASP |
| `performance-optimizer` | Slow queries, asset optimization, caching, DB bloat |
| `plugin-developer` | Scaffold and develop custom plugins following APH patterns |
| `frontend-developer` | SCSS, JS, Gulp, responsive design, WCAG accessibility |

## Common Commands

```bash
# Environment
ddev start / ddev stop / ddev restart
ddev ssh                              # Shell into web container
ddev describe                         # Show project info/URLs

# WordPress
ddev wp plugin list                   # List plugins
ddev wp cache flush                   # Clear object cache
ddev wp rewrite flush                 # Regenerate permalinks
ddev wp transient delete --all        # Clear transients
ddev wp user list --role=administrator # List admin users

# Database
ddev setup-db                         # Import DB with search-replace
ddev mysql                            # MySQL CLI
ddev snapshot                         # Snapshot current DB
ddev snapshot restore                 # Restore snapshot

# Theme Build
ddev ssh && cd web/app/themes/mightily && npm run gulp

# Debugging
ddev exec tail -100 /var/www/html/web/app/debug.log
ddev logs -f
ddev xdebug on
```

## Key Files

| File | Purpose |
|------|---------|
| `config/application.php` | All env-driven configuration and constants |
| `web/app/themes/mightily/functions/wc-hooks.php` | Central WooCommerce hooks registry |
| `web/app/themes/mightily/functions/wc/*.php` | WooCommerce implementations |
| `web/app/themes/mightily/classes/APH/*.php` | Business logic (Order, Products, Ajax, Fields) |
| `web/app/themes/mightily/gulpfile.js` | Build pipeline config |
| `.env.example` | All environment variables documented |
| `CLAUDE.md` | Full project documentation |

## Accessibility Note

APH (American Printing House for the Blind) serves blind and visually impaired users. All frontend work must meet WCAG 2.1 AA standards. Use the `frontend-developer` agent for any UI changes.
