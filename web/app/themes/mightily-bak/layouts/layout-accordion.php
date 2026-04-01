<?php
    $display_intro = get_sub_field('display_intro');
    $title = get_sub_field('title');
    $subtitle = get_sub_field('subtitle');
    $view = get_sub_field('view');
    $margin_bottom = get_sub_field('layout_spacing');
?>

<section id="accordion-section-<?php echo $layout_counter; ?>" class="layout accordions <?php echo $view; ?>" style="margin-bottom: <?php echo $margin_bottom; ?>px;">

    <?php include(locate_template('layouts/component-intro.php')); ?>

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
                                            <h1 class="bx--accordion__title"><?php the_sub_field('title'); ?></h1>
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
                                <h1 class="h5 title"><a href="#item-<?php echo $layout_counter.'-'.$i; ?>"><?php the_sub_field('title'); ?></a></h1>
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
