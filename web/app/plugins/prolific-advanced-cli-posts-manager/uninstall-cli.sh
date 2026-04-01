#!/bin/bash

# Prolific CLI Posts Manager - Uninstall Script
# This script removes the global CLI command and aliases

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}Prolific CLI Posts Manager - Uninstallation${NC}"
echo "============================================="
echo

COMMAND_WRAPPER="/usr/local/bin/prolific"

# Remove global command
if [ -f "$COMMAND_WRAPPER" ]; then
    if [ ! -w "/usr/local/bin" ]; then
        echo -e "${YELLOW}Removing global 'prolific' command (requires sudo)...${NC}"
        sudo rm -f "$COMMAND_WRAPPER"
    else
        echo -e "${YELLOW}Removing global 'prolific' command...${NC}"
        rm -f "$COMMAND_WRAPPER"
    fi
    echo -e "${GREEN}✓ Global 'prolific' command removed${NC}"
else
    echo -e "${YELLOW}Global 'prolific' command not found (already removed?)${NC}"
fi

# Remove shell aliases
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

if [ -n "$SHELL_RC" ] && [ -f "$SHELL_RC" ]; then
    if grep -q "# Prolific CLI Posts Manager aliases" "$SHELL_RC" 2>/dev/null; then
        echo -e "${YELLOW}Removing shell aliases from $SHELL_RC...${NC}"
        
        # Create a temporary file without the prolific aliases
        sed '/# Prolific CLI Posts Manager aliases/,/^$/d' "$SHELL_RC" > "${SHELL_RC}.tmp"
        mv "${SHELL_RC}.tmp" "$SHELL_RC"
        
        echo -e "${GREEN}✓ Shell aliases removed${NC}"
        echo -e "${YELLOW}Restart your terminal to complete alias removal${NC}"
    fi
fi

echo
echo -e "${GREEN}Uninstallation Complete!${NC}"
echo
echo "The 'prolific' command and aliases have been removed."
echo "You can still use the standalone runner directly:"
echo "  php /path/to/plugin/standalone-runner.php --help"
echo