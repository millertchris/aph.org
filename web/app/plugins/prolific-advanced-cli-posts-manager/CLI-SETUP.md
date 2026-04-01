# Prolific CLI Posts Manager - Command Line Setup

## Quick Installation

**On your staging server, navigate to the plugin directory and run:**

```bash
cd /sites/aph-staging.prolificdigital.io/files/web/app/plugins/prolific-advanced-cli-posts-manager
./install-cli.sh
```

This will:
- Create a global `prolific` command
- Add convenient shell aliases
- Set up everything automatically

## Usage After Installation

### Basic Commands
```bash
# Show help
prolific --help

# List all posts
prolific --posts

# List draft posts
prolific --posts --status=draft

# Dry run delete of old posts
prolific --operation=delete --posts --date-to=2023-01-01 --dry-run
```

### Quick Aliases (after terminal restart)
```bash
prolific-posts       # Same as 'prolific --posts'
prolific-list        # List all posts  
prolific-help        # Show help
```

### Advanced Examples
```bash
# Export backup of news category
prolific --operation=export-backup --posts --category=news

# Modify draft posts to published
prolific --operation=modify --posts --status=draft --modify-status=publish

# Clean up WooCommerce products
prolific --woocommerce-products --stock-status=outofstock --dry-run

# Database maintenance  
prolific --operation=cleanup-database --orphaned-meta --dry-run
```

## Manual Usage (Alternative)

If you prefer not to install globally, you can run the standalone script directly:

```bash
cd /path/to/plugin/directory
php standalone-runner.php --posts
```

## Uninstall

To remove the global command and aliases:

```bash
cd /path/to/plugin/directory
./uninstall-cli.sh
```

## Troubleshooting

**Command not found after installation:**
```bash
# Restart your terminal or run:
source ~/.bashrc  # or ~/.zshrc
```

**Permission issues:**
```bash
# The installer will prompt for sudo if needed
# Make sure you have administrator access
```

**Plugin path issues:**
```bash
# Make sure you're in the correct plugin directory
pwd
# Should show: .../wp-content/plugins/prolific-advanced-cli-posts-manager
```

## Features

✅ **Bypasses WP-CLI conflicts** - Avoids WooCommerce/PHP compatibility issues  
✅ **Full WordPress access** - All WordPress functions available  
✅ **Same powerful features** - All filtering and operations supported  
✅ **Easy installation** - One command setup  
✅ **Global accessibility** - Use from anywhere on your server  

## Support

The CLI tool supports all the same operations and filters as documented in the WordPress admin interface under **Tools → CLI Posts Manager**.