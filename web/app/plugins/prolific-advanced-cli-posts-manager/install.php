<?php
/**
 * Prolific CLI Posts Manager - User-Friendly Installer
 * No sudo required! Creates aliases and user-local commands
 */

echo "Prolific CLI Posts Manager - Installation\n";
echo "=========================================\n\n";

// Get current directory
$plugin_dir = dirname(__FILE__);
echo "Plugin directory: $plugin_dir\n";

// Check if standalone runner exists
$runner_script = $plugin_dir . '/standalone-runner.php';
echo "Runner script: $runner_script\n";
echo "Runner exists: " . (file_exists($runner_script) ? "YES" : "NO") . "\n\n";

if (!file_exists($runner_script)) {
    echo "❌ ERROR: standalone-runner.php not found!\n";
    echo "Please ensure the plugin is properly installed.\n";
    exit(1);
}

// Installation methods (no sudo required)
echo "🚀 Installing Prolific CLI (No sudo required)...\n\n";

$success_methods = array();

// Method 1: Try user local bin
$user_bin = $_SERVER['HOME'] . '/bin';
if (create_user_local_command($plugin_dir, $user_bin)) {
    $success_methods[] = "User local command (~/$user_bin/prolific)";
    echo "✅ Method 1 SUCCESS: User local command installed\n";
    echo "   Command available at: ~/bin/prolific\n";
    echo "   Make sure ~/bin is in your PATH\n\n";
} else {
    echo "❌ Method 1 FAILED: Could not create user local command\n\n";
}

// Method 2: Create shell aliases
if (create_shell_aliases($plugin_dir)) {
    $success_methods[] = "Shell aliases";
    echo "✅ Method 2 SUCCESS: Shell aliases created\n";
    echo "   Restart your terminal or run: source ~/.bashrc\n\n";
} else {
    echo "❌ Method 2 FAILED: Could not create shell aliases\n\n";
}

// Method 3: Create a wrapper script in current directory
$local_wrapper = $plugin_dir . '/prolific';
if (create_local_wrapper($plugin_dir, $local_wrapper)) {
    $success_methods[] = "Local wrapper script";
    echo "✅ Method 3 SUCCESS: Local wrapper created\n";
    echo "   Use: ./prolific or php $local_wrapper\n\n";
} else {
    echo "❌ Method 3 FAILED: Could not create local wrapper\n\n";
}

// Method 4: Try global installation only if we have permission
$global_bin = '/usr/local/bin/prolific';
if (is_writable('/usr/local/bin') && create_global_command($plugin_dir, $global_bin)) {
    $success_methods[] = "Global command";
    echo "✅ Method 4 SUCCESS: Global command installed\n";
    echo "   Command available globally: prolific\n\n";
}

// Results
echo "📊 INSTALLATION RESULTS:\n";
echo "========================\n";

if (!empty($success_methods)) {
    echo "🎉 SUCCESS! Installed via: " . implode(", ", $success_methods) . "\n\n";
    
    echo "💡 HOW TO USE:\n";
    if (in_array("Global command", $success_methods)) {
        echo "• Global: prolific --posts\n";
    }
    if (in_array("User local command (~/$user_bin/prolific)", $success_methods)) {
        echo "• User local: prolific --posts (if ~/bin is in PATH)\n";
    }
    if (in_array("Shell aliases", $success_methods)) {
        echo "• Alias: prolific-posts (after terminal restart)\n";
    }
    if (in_array("Local wrapper script", $success_methods)) {
        echo "• Local: ./prolific --posts (from plugin directory)\n";
    }
    echo "• Direct: php $runner_script --posts\n\n";
    
    echo "🧪 TEST COMMANDS:\n";
    echo "• prolific --help\n";
    echo "• prolific --posts --status=publish\n";
    echo "• prolific --operation=delete --posts --status=draft --dry-run\n\n";
    
} else {
    echo "⚠️  No automatic installation methods succeeded.\n";
    echo "   You can still use the standalone runner directly:\n";
    echo "   php $runner_script --help\n\n";
}

echo "✨ Installation complete!\n";

// Helper functions
function create_user_local_command($plugin_dir, $user_bin) {
    if (!is_dir($user_bin)) {
        if (!@mkdir($user_bin, 0755, true)) {
            return false;
        }
    }
    
    if (!is_writable($user_bin)) {
        return false;
    }
    
    $command_file = $user_bin . '/prolific';
    $wrapper_content = create_wrapper_content($plugin_dir);
    
    if (file_put_contents($command_file, $wrapper_content) && chmod($command_file, 0755)) {
        return true;
    }
    
    return false;
}

function create_shell_aliases($plugin_dir) {
    $shell_files = array();
    $home = $_SERVER['HOME'];
    
    // Detect shell and add appropriate RC files
    if (file_exists($home . '/.zshrc')) {
        $shell_files[] = $home . '/.zshrc';
    }
    if (file_exists($home . '/.bashrc')) {
        $shell_files[] = $home . '/.bashrc';
    }
    if (file_exists($home . '/.bash_profile')) {
        $shell_files[] = $home . '/.bash_profile';
    }
    
    $success = false;
    
    foreach ($shell_files as $shell_file) {
        if (is_writable($shell_file)) {
            $aliases = "\n# Prolific CLI Posts Manager aliases\n";
            $aliases .= "alias prolific='php \"$plugin_dir/standalone-runner.php\"'\n";
            $aliases .= "alias prolific-posts='php \"$plugin_dir/standalone-runner.php\" --posts'\n";
            $aliases .= "alias prolific-help='php \"$plugin_dir/standalone-runner.php\" --help'\n";
            
            // Check if aliases already exist
            $content = file_get_contents($shell_file);
            if (strpos($content, 'Prolific CLI Posts Manager aliases') === false) {
                if (file_put_contents($shell_file, $aliases, FILE_APPEND)) {
                    $success = true;
                }
            } else {
                $success = true; // Already exists
            }
        }
    }
    
    return $success;
}

function create_local_wrapper($plugin_dir, $wrapper_file) {
    $wrapper_content = create_wrapper_content($plugin_dir);
    
    if (file_put_contents($wrapper_file, $wrapper_content) && chmod($wrapper_file, 0755)) {
        return true;
    }
    
    return false;
}

function create_global_command($plugin_dir, $command_file) {
    $wrapper_content = create_wrapper_content($plugin_dir);
    
    if (file_put_contents($command_file, $wrapper_content) && chmod($command_file, 0755)) {
        return true;
    }
    
    return false;
}

function create_wrapper_content($plugin_dir) {
    return <<<EOF
#!/bin/bash
# Prolific CLI Posts Manager - Command Wrapper
# Auto-generated wrapper script

PLUGIN_DIR="$plugin_dir"
RUNNER_SCRIPT="\$PLUGIN_DIR/standalone-runner.php"

if [ ! -f "\$RUNNER_SCRIPT" ]; then
    echo "Error: Prolific CLI runner not found at \$RUNNER_SCRIPT"
    echo "Please ensure the plugin is properly installed."
    exit 1
fi

# Forward all arguments to the runner
php "\$RUNNER_SCRIPT" "\$@"
EOF;
}
?>