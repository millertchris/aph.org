<?php if (show_env_banner()) : ?>

    <div class="notice site-wide notice-small">
        <div class="wrapper">
            <div class="content">
                <p>
                    <span>Environment: <?php echo get_current_env(); ?> — </span>
                    <span>Current roles: <?php display_users_roles(); ?></span>
                
                </p>
            </div>
        </div>
    </div>
    
<?php endif; ?>