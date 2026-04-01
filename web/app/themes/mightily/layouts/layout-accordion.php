<?php
    $display_intro = get_sub_field('display_intro');
    $title = get_sub_field('title');
    $subtitle = get_sub_field('subtitle');
    $view = get_sub_field('view');
    $margin_bottom = get_sub_field('layout_spacing');
?>

<section id="accordion-section-<?php echo $layout_counter; ?>" class="layout accordions <?php echo $view; ?>" style="margin-bottom: <?php echo $margin_bottom; ?>px;">

    <?php include(locate_template('layouts/component-intro.php')); ?>
    <?php
        if($layout_counter == 1){
            $accordion_heading_tag = 'h2';
        } else {
            if($display_intro) {
                $accordion_heading_tag = 'h3';
            } else {
                $accordion_heading_tag = 'h2';
            }                  
        }
    ?> 
    <div class="wrapper">
        <div class="row">
            <div class="col">

                <?php if ($view == 'list'): ?>

                    <?php if (have_rows('acc_item')): ?>
                        <div class="accordion-wrapper">
                            <ul data-accordion class="bx--accordion tabs wc-tabs">
                                <?php $i = 1; ?>
                                <?php while (have_rows('acc_item')): the_row(); ?>
                                    <li data-accordion-item class="bx--accordion__item tab">
                                        <button class="bx--accordion__heading h4" aria-expanded="false" aria-controls="pane-<?php echo $layout_counter.'-'.$i; ?>">
                                            <<?php echo $accordion_heading_tag; ?> class="bx--accordion__title"><?php the_sub_field('title'); ?></<?php echo $accordion_heading_tag; ?>>
                                        </button>
                                        <div id="pane-<?php echo $layout_counter.'-'.$i; ?>" class="bx--accordion__content">
                                            <div class="bx--accordion__content-wrapper">
                                                <?php the_sub_field('content'); ?>
                                                <?php include(locate_template('layouts/component-button.php')); ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php $i++; endwhile; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                <?php endif; ?>

                <?php if ($view == 'tabbed'): ?>
                    <?php if (have_rows('acc_item')): $i = 0; ?>
                    	<ul class="acc-list">
                    	<?php while (have_rows('acc_item')): the_row(); $i++; ?>

                    		<li class="acc-title">
                                <?php if($layout_counter == 1) : ?>
                                    <h2 class="h5 title"><a href="#item-<?php echo $layout_counter.'-'.$i; ?>"><?php the_sub_field('title'); ?></a></h2>
                                <?php else : ?>
                                    <h3 class="h5 title"><a href="#item-<?php echo $layout_counter.'-'.$i; ?>"><?php the_sub_field('title'); ?></a></h3>
                                <?php endif; ?>                                
                    		</li>

                    	<?php endwhile; ?>
                    	</ul>
                    <?php endif; ?>

                    <?php if (have_rows('acc_item')): $i = 0; ?>

                    	<?php while (have_rows('acc_item')): the_row(); $i++; ?>

                    		<div id="item-<?php echo $layout_counter.'-'.$i; ?>" class="acc-item" tabindex="0">
                                <div class="acc-content">
                                    <?php the_sub_field('content'); ?>
                                    <?php include(locate_template('layouts/component-button.php')); ?>
                                </div>
                    		</div>

                    	<?php endwhile; ?>

                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>
