jQuery(document).ready(function($) {
    
    // Tab functionality
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var target = $(this).attr('href');
        
        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show target content
        $('.tab-content').removeClass('active');
        $(target).addClass('active');
        
        // Load dynamic content for specific tabs
        if (target === '#stats' && !$('#stats-data').hasClass('loaded')) {
            loadStats();
        } else if (target === '#backups' && !$('#backups-data').hasClass('loaded')) {
            loadBackups();
        }
    });
    
    // Refresh stats button
    $('#refresh-stats').on('click', function(e) {
        e.preventDefault();
        loadStats();
    });
    
    // Refresh backups button
    $('#refresh-backups').on('click', function(e) {
        e.preventDefault();
        loadBackups();
    });
    
    // Load stats via AJAX
    function loadStats() {
        $('#stats-loading').show();
        $('#stats-content button').prop('disabled', true);
        
        $.ajax({
            url: prolific_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'prolific_get_post_stats',
                nonce: prolific_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderStats(response.data);
                    $('#stats-data').addClass('loaded');
                } else {
                    $('#stats-data').html('<div class="notice notice-error"><p>Failed to load statistics.</p></div>');
                }
            },
            error: function() {
                $('#stats-data').html('<div class="notice notice-error"><p>Error loading statistics.</p></div>');
            },
            complete: function() {
                $('#stats-loading').hide();
                $('#stats-content button').prop('disabled', false);
            }
        });
    }
    
    // Render stats data
    function renderStats(stats) {
        var html = '<div class="stats-grid">';
        
        $.each(stats, function(postType, data) {
            html += '<div class="stat-card">';
            html += '<h3>' + escapeHtml(data.label) + '</h3>';
            
            $.each(data.counts, function(status, count) {
                if (count > 0) {
                    html += '<div class="stat-item">';
                    html += '<span class="stat-label">' + escapeHtml(status) + '</span>';
                    html += '<span class="stat-value">' + parseInt(count).toLocaleString() + '</span>';
                    html += '</div>';
                }
            });
            
            html += '<div class="stat-item">';
            html += '<span class="stat-label">Total</span>';
            html += '<span class="stat-value">' + parseInt(data.total).toLocaleString() + '</span>';
            html += '</div>';
            
            html += '</div>';
        });
        
        html += '</div>';
        $('#stats-data').html(html);
    }
    
    // Load backups via AJAX
    function loadBackups() {
        $('#backups-loading').show();
        $('#backups-content button').prop('disabled', true);
        
        $.ajax({
            url: prolific_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'prolific_list_backups',
                nonce: prolific_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderBackups(response.data);
                    $('#backups-data').addClass('loaded');
                } else {
                    var errorMsg = response.data || 'Unknown error';
                    $('#backups-data').html('<div class="notice notice-error"><p>Failed to load backups: ' + errorMsg + '</p></div>');
                }
            },
            error: function(xhr, status, error) {
                $('#backups-data').html('<div class="notice notice-error"><p>Error loading backups: ' + error + '</p></div>');
            },
            complete: function() {
                $('#backups-loading').hide();
                $('#backups-content button').prop('disabled', false);
            }
        });
    }
    
    // Render backups data
    function renderBackups(backups) {
        var html = '';
        
        if (backups.length === 0) {
            html = '<div class="notice notice-info"><p>No backup files found. Backups are created automatically when you delete posts using the CLI commands.</p></div>';
        } else {
            html = '<div class="backups-grid">';
            
            $.each(backups, function(index, backup) {
                html += '<div class="backup-item">';
                
                html += '<div class="backup-info">';
                html += '<h4>' + escapeHtml(backup.filename) + '</h4>';
                html += '<div class="backup-meta">';
                html += 'Created: ' + escapeHtml(backup.created) + '<br>';
                html += 'Posts: ' + escapeHtml(backup.post_count);
                html += '</div>';
                html += '</div>';
                
                html += '<div class="backup-size">';
                html += escapeHtml(backup.size);
                html += '</div>';
                
                html += '<div class="backup-actions">';
                html += '<button class="button button-secondary view-backup-info" data-filename="' + escapeHtml(backup.filename) + '">';
                html += '<span class="dashicons dashicons-visibility"></span> View Details';
                html += '</button>';
                html += '</div>';
                
                html += '</div>';
            });
            
            html += '</div>';
            
            html += '<div style="margin-top: 20px; padding: 15px; background: #f0f6fc; border: 1px solid #c3c4c7; border-radius: 4px;">';
            html += '<h4>About Backup Files:</h4>';
            html += '<p>These JSON backup files contain complete post data including meta fields, taxonomies, and comments.</p>';
            html += '<p><strong>Note:</strong> Restore functionality is currently only available through the WordPress admin interface or direct PHP scripting.</p>';
            html += '<p>Backup files are automatically created before any delete operations to ensure data safety.</p>';
            html += '</div>';
        }
        
        $('#backups-data').html(html);
        
        // Add click handlers for view details buttons
        $('.view-backup-info').on('click', function() {
            var filename = $(this).data('filename');
            
            // Show details in a simple alert for now
            // In a real implementation, this could open a modal with detailed backup info
            alert('Backup Details:\n\nFilename: ' + filename + '\n\nThis backup contains complete post data including:\n- Post content and metadata\n- Custom fields\n- Taxonomy relationships\n- Comments\n\nBackup files are automatically created before delete operations to ensure data safety.');
        });
    }
    
    // Copy text to clipboard
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            var textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            document.execCommand('copy');
            textArea.remove();
        }
    }
    
    // Escape HTML
    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) {
            return '';
        }
        // Convert to string if it's not already
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    // Copy functionality for installation tab
    $(document).on('click', '.copy-btn', function(e) {
        e.preventDefault();
        var textToCopy = $(this).data('copy');
        copyToClipboard(textToCopy);
        
        // Visual feedback
        var $button = $(this);
        var originalText = $button.text();
        $button.text('Copied!').addClass('copied');
        
        setTimeout(function() {
            $button.text(originalText).removeClass('copied');
        }, 2000);
    });
    
    // Copy command functionality for command examples
    $(document).on('click', '.command-item code', function() {
        var command = $(this).text().trim();
        copyToClipboard(command);
        
        // Visual feedback
        var $code = $(this);
        $code.css('background-color', '#00a32a');
        setTimeout(function() {
            $code.css('background-color', '#2c3338');
        }, 500);
        
        // Show tooltip or notification
        if (!$('.copy-notification').length) {
            $('body').append('<div class="copy-notification" style="position: fixed; top: 32px; right: 20px; background: #00a32a; color: white; padding: 10px 15px; border-radius: 4px; z-index: 999999; font-size: 13px;">Command copied to clipboard!</div>');
            
            setTimeout(function() {
                $('.copy-notification').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 2000);
        }
    });
    
    // Add hover effect for code blocks
    $('.command-item code').hover(
        function() {
            $(this).css({
                'cursor': 'pointer',
                'box-shadow': '0 0 0 2px rgba(50, 195, 120, 0.3)'
            });
        },
        function() {
            $(this).css({
                'cursor': 'default',
                'box-shadow': 'none'
            });
        }
    );
    
    // Settings form validation
    $('#batch_size, #backup_retention, #log_retention').on('input', function() {
        var $input = $(this);
        var min = parseInt($input.attr('min'));
        var max = parseInt($input.attr('max'));
        var value = parseInt($input.val());
        
        if (value < min || value > max || isNaN(value)) {
            $input.css('border-color', '#d63638');
        } else {
            $input.css('border-color', '#8c8f94');
        }
    });
    
    // Auto-load stats tab if it's the active tab on page load
    if ($('#stats').hasClass('active')) {
        loadStats();
    }
    
    // Auto-load backups tab if it's the active tab on page load
    if ($('#backups').hasClass('active')) {
        loadBackups();
    }
});

// Add some CSS for the copy notification via JavaScript since we can't modify PHP templates
jQuery(document).ready(function($) {
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .command-item code:hover::after {
                content: "Click to copy";
                position: absolute;
                background: rgba(0, 0, 0, 0.8);
                color: white;
                padding: 4px 8px;
                border-radius: 3px;
                font-size: 11px;
                margin-left: 10px;
                pointer-events: none;
                z-index: 1000;
            }
            .command-item {
                position: relative;
            }
        `)
        .appendTo('head');
});