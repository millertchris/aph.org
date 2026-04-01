<?php
    $card_size = get_sub_field('card_size');
    $card_fill = get_sub_field('card_fill');
    $margin_bottom = get_sub_field('layout_spacing');
?>

<section class="layout cards <?php echo $card_size; ?>" style="margin-bottom: <?php echo $margin_bottom; ?>px;">
    <div class="content">
        <div class="wrapper">

            <?php
                $intro = get_sub_field('intro');
                $display_intro = $intro['display_intro'];
                $title = $intro['title'];
                $subtitle = $intro['subtitle'];
                $heading_type = '';
                $style = $intro['styles'];

                if ($style == 'style-1') {
                    $heading_size = 'h2';
                } else {
                    $heading_size = 'h4';
                }

                if($display_intro) {
                  $heading_type = 'h2';
                } else {
                  $heading_type = 'h1';
                }

                $classes = $style;
            ?>
            <?php if ($display_intro): ?>
                <div class="intro <?php echo $classes; ?>">
                    <div class="wrapper">
                        <h1 class="<?php echo $heading_size; ?> title"><?php echo $title; ?></h1>
                        <?php if ($subtitle): ?>
                            <p class="summary"><?php echo $subtitle; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (have_rows('cards')): ?>
                <div class="card-list">
            	<?php while (have_rows('cards')): the_row();
                $card_title = get_sub_field('title');
                $card_content = get_sub_field('content');
              ?>
                <?php if (get_sub_field('display_button')): ?>
                  <?php if ( have_rows('button') ) : ?>
                    <?php while( have_rows('button') ) : the_row(); ?>
                      <?php
                        $button = get_field('button');
                        $link = get_sub_field('link');
                        if ($link) {
                            $link_url = $link['url'];
                            $link_title = $link['title'];
                            $link_target = $link['target'];

                            if ($link_target == NULL) {
                                $link_target = '_self';
                            }
                        }
                      ?>
                      <a href="<?php echo $link_url; ?>" target="<?php echo $link_target; ?>" class="card btn accessible-card <?php echo $card_fill; ?>">
                        <div class="content">
                          <<?php echo $heading_type; ?> class="h4"><?php echo $card_title; ?></<?php echo $heading_type; ?>>
                          <?php echo $card_content; ?>
                        </div>
                        <div class="buttons">
                          <span class="btn"><?php echo $link_title; ?></span>
                        </div>
                      </a>
                    <?php endwhile; ?>
                  <?php endif; ?>
                <?php else : ?>
                  <div class="card btn accessible-card <?php echo $card_fill; ?>">
                    <div class="content">
                      <<?php echo $heading_type; ?> class="h4"><?php echo $card_title; ?></<?php echo $heading_type; ?>>
                      <?php echo $card_content; ?>
                    </div>
                  </div>
                <?php endif; ?>

            	<?php endwhile; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>
