<?php if (get_sub_field('display_button')): ?>
    <?php if ( have_rows('button') ) : ?>
        <div class="buttons">
        <?php while( have_rows('button') ) : the_row(); ?>
            <?php
                $link = get_sub_field('link');
                if ($link) {
                    $link_url = $link['url'];
                    $link_title = $link['title'];
                    $link_target = $link['target'];

                    if ($link_target == NULL) {
                        $link_target = '_self';
                    }

                    $a11y = '';
                    $icon = '';
                    if($link_target == '_blank'){
                        $a11y = ' rel="noopener" aria-describedby="new-window-message"';
                        $icon = ' <i aria-hidden="true" class="fas fa-external-link-alt"></i>';
                    }

                    echo '<a href="' . $link_url . '" target="' . $link_target . '" class="btn"' . $a11y . '>' . $link_title . $icon . '</a>';
                }
            ?>
        <?php endwhile; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>
