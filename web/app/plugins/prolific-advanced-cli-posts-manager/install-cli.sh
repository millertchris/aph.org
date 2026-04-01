#!/bin/bash

# Prolific CLI Posts Manager - Installation Script
# This script sets up the CLI command globally

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}Prolific CLI Posts Manager - Installation${NC}"
echo "========================================"
echo

# Get the current directory (plugin directory)
PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RUNNER_SCRIPT="$PLUGIN_DIR/standalone-runner.php"

# Check if standalone runner exists
if [ ! -f "$RUNNER_SCRIPT" ]; then
    echo -e "${RED}Error: standalone-runner.php not found in $PLUGIN_DIR${NC}"
    exit 1
fi

# Create the prolific command wrapper
COMMAND_WRAPPER="/usr/local/bin/prolific"

# Check if we need sudo
if [ ! -w "/usr/local/bin" ]; then
    echo -e "${YELLOW}Creating global 'prolific' command (requires sudo)...${NC}"
    
    # Create the command wrapper with sudo
    sudo tee "$COMMAND_WRAPPER" > /dev/null <<EOF
#!/bin/bash
# Prolific CLI Posts Manager - Global Command
# Auto-generated wrapper script

PLUGIN_DIR="$PLUGIN_DIR"
RUNNER_SCRIPT="\$PLUGIN_DIR/standalone-runner.php"

if [ ! -f "\$RUNNER_SCRIPT" ]; then
    echo "Error: Prolific CLI runner not found at \$RUNNER_SCRIPT"
    echo "Please ensure the plugin is properly installed."
    exit 1
fi

# Forward all arguments to the runner
php "\$RUNNER_SCRIPT" "\$@"
EOF

    # Make it executable
    sudo chmod +x "$COMMAND_WRAPPER"
    
    echo -e "${GREEN}✓ Global 'prolific' command created successfully!${NC}"
else
    echo -e "${YELLOW}Creating global 'prolific' command...${NC}"
    
    # Create the command wrapper without sudo
    tee "$COMMAND_WRAPPER" > /dev/null <<EOF
#!/bin/bash
# Prolific CLI Posts Manager - Global Command
# Auto-generated wrapper script

PLUGIN_DIR="$PLUGIN_DIR"
RUNNER_SCRIPT="\$PLUGIN_DIR/standalone-runner.php"

if [ ! -f "\$RUNNER_SCRIPT" ]; then
    echo "Error: Prolific CLI runner not found at \$RUNNER_SCRIPT"
    echo "Please ensure the plugin is properly installed."
    exit 1
fi

# Forward all arguments to the runner
php "\$RUNNER_SCRIPT" "\$@"
EOF

    # Make it executable
    chmod +x "$COMMAND_WRAPPER"
    
    echo -e "${GREEN}✓ Global 'prolific' command created successfully!${NC}"
fi

# Add shell aliases for convenience
SHELL_RC=""
if [ -n "$BASH_VERSION" ]; then
    SHELL_RC="$HOME/.bashrc"
elif [ -n "$ZSH_VERSION" ]; then
    SHELL_RC="$HOME/.zshrc"
else
    # Try to detect shell
    if [ -f "$HOME/.zshrc" ]; then
        SHELL_RC="$HOME/.zshrc"
    elif [ -f "$HOME/.bashrc" ]; then
        SHELL_RC="$HOME/.bashrc"
    fi
fi

if [ -n "$SHELL_RC" ]; then
    # Check if alias already exists
    if ! grep -q "alias prolific-posts=" "$SHELL_RC" 2>/dev/null; then
        echo -e "${YELLOW}Adding shell aliases...${NC}"
        
        cat >> "$SHELL_RC" <<EOF

# Prolific CLI Posts Manager aliases
alias prolific-posts='prolific'
alias prolific-help='prolific --help'
alias prolific-list='prolific --operation=list --posts'
alias prolific-stats='prolific --operation=stats'
EOF
        
        echo -e "${GREEN}✓ Shell aliases added to $SHELL_RC${NC}"
        echo -e "${YELLOW}Run 'source $SHELL_RC' or restart your terminal to use aliases${NC}"
    fi
fi

echo
echo -e "${GREEN}Installation Complete!${NC}"
echo
echo "You can now use the following commands:"
echo
echo -e "${BLUE}Global Command:${NC}"
echo "  prolific --help                    # Show help"
echo "  prolific --posts                   # List all posts"
echo "  prolific --operation=delete --posts --status=draft --dry-run"
echo
echo -e "${BLUE}Quick Aliases (after shell restart):${NC}"
echo "  prolific-posts                     # Same as 'prolific --posts'"
echo "  prolific-list                      # List all posts"
echo "  prolific-help                      # Show help"
echo
echo -e "${BLUE}Examples:${NC}"
echo "  prolific --posts --status=draft"
echo "  prolific --operation=delete --posts --date-to=2023-01-01 --dry-run"
echo "  prolific --operation=export-backup --posts --category=news"
echo "  prolific --woocommerce-products --stock-status=outofstock"
echo
echo -e "${YELLOW}Note: This bypasses WP-CLI to avoid WooCommerce conflicts${NC}"
echo