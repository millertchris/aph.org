# APH.org

The official website for [American Printing House for the Blind](https://www.aph.org) (APH) — the world's largest nonprofit organization dedicated to creating accessible educational and daily living products for people who are blind or visually impaired. Founded in 1858, APH produces materials and tools that empower independence and learning.

This repository contains the full WordPress site powering [aph.org](https://www.aph.org), including the e-commerce storefront (WooCommerce), custom theme, and all plugins.

## Technology Stack

- **CMS**: WordPress on the [Bedrock](https://roots.io/bedrock/) boilerplate
- **E-commerce**: WooCommerce with custom extensions
- **Theme**: Mightily (custom)
- **Media**: AWS S3 via WP Offload Media (served from media.aph.org)
- **Local Development**: [DDEV](https://ddev.readthedocs.io/) (Docker-based)
- **PHP**: 8.4 / **Database**: MariaDB 10.11

## Repository Structure

```
aph.org/
├── config/                  # Bedrock configuration
│   ├── application.php      # Main config (reads from .env)
│   └── environments/        # Per-environment overrides
├── web/
│   ├── app/                 # WordPress content
│   │   ├── plugins/         # All plugins (git-tracked)
│   │   ├── themes/mightily/ # Custom theme
│   │   └── mu-plugins/      # Must-use plugins
│   └── wp/                  # WordPress core (Composer-managed, gitignored)
├── .ddev/                   # Local dev container configuration
├── db/                      # Database imports (gitignored)
├── composer.json            # PHP dependencies
└── .env.example             # Environment variable template
```

---

## Local Development

### Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) or Colima
- [DDEV](https://ddev.readthedocs.io/en/stable/) (v1.22+)

### Getting Started

```bash
# 1. Clone the repository
git clone https://github.com/millertchris/aph.org.git
cd aph.org

# 2. Start DDEV (installs WordPress core and dependencies automatically)
ddev start

# 3. Import the database (place SQL dump in db/ first)
ddev setup-db

# 4. Reset your admin password
ddev wp user update <username> --user_pass='password' --skip-plugins --skip-themes

# 5. Open the site
ddev launch
```

### Local URLs

- **Site**: https://aph.ddev.site
- **Admin**: https://aph.ddev.site/wp/wp-admin

### Database

Obtain a database export from production and place the `.sql` or `.sql.gz` file in the `db/` directory. The `ddev setup-db` command handles everything:

- Imports the database
- Runs search-replace (`www.aph.org` -> `aph.ddev.site`)
- Deactivates production-only plugins (WPMU Defender — 2FA/reCAPTCHA breaks locally)
- Flushes rewrite rules and caches

For incremental syncs, configure WP Migrate DB Pro by adding your license to `.env`.

### Media / S3

Media files are served from the production S3 bucket via WP Offload Media. No local media setup is required — images and files display automatically from `media.aph.org`.

### Common Commands

```bash
ddev start / ddev stop          # Start or stop the environment
ddev setup-db                   # Import and configure database
ddev wp <command>               # Run WP-CLI commands
ddev composer update            # Update PHP dependencies
ddev logs -f                    # Follow container logs
ddev xdebug on                  # Enable step debugging (port 9003)
ddev ssh                        # SSH into the web container
```

### Updating WordPress

WordPress core is managed via Composer:
```bash
ddev composer update roots/wordpress
```

### Theme Development

The active theme is `mightily` at `web/app/themes/mightily/`.

```bash
ddev ssh
cd web/app/themes/mightily
npm install
npm run dev
```

## Architecture

This project uses **Bedrock** by Roots, which provides:

- **Environment-based configuration** — all secrets and settings in `.env`, never hardcoded
- **Improved security** — WordPress content directory (`web/app/`) separated from core (`web/wp/`)
- **Composer for WordPress core** — version-locked, installed automatically on `ddev start`
- **Git-tracked plugins** — all 77 plugins committed to the repo for reliable, auth-free onboarding

For detailed setup instructions and troubleshooting, see [docs/SETUP.md](docs/SETUP.md).
