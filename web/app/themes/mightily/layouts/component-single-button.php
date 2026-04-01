<?php if (get_sub_field('enable_button')): ?>
    <?php if ( get_sub_field('button') ) : ?>
        <div class="buttons">
            <?php
                $button = get_sub_field('button');
                if ($button) {
                    $button_url = $button['url'];
                    $button_title = $button['title'];
                    $button_target = $button['target'];

                    if ($button_target == NULL) {
                        $button_target = '_self';
                    }

                    $a11y = '';
                    $icon = '';
                    if($button_target == '_blank'){
                        $a11y = ' rel="noopener" aria-describedby="new-window-message"';
                        $icon = ' <i aria-hidden="true" class="fas fa-external-link-alt"></i>';
                    }

                    echo '<a href="' . $button_url . '" target="' . $button_target . '" class="btn"' . $a11y . '>' . $button_title . $icon . '</a>';
                }
            ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
