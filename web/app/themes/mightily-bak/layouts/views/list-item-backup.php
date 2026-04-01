<?php if ($posts) : ?>

    <?php if ($search == 'enable_search'): ?>
        <form class="filter active filter-search" role="search">
          <div class="input-wrapper text-input-field">
            <!-- Loop through categories to create a select list to filter with -->
            <!-- <select class="select-filter" name="">
                <option value="test">Test</option>
            </select> -->
            <label class="filter-label"  for="text-filter">Start your search</label>
            <input type="text" class="text-filter" id="text-filter" name="text-filter" value="" placeholder="Start your Search">
          </div>
            <input class="text-filter-button" type="submit" value="Search">
        </form>
    <?php endif; ?>

    <ul class="list-items <?php echo $post_type; ?>-items">
        <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>

            <li class="item <?php echo $post_type; ?>-item">

				<?php if ($post_type == 'people'): ?>
					<div class="left">
						<div class="image">
							<?php $thumbnail_id = get_post_thumbnail_id(get_the_id()); ?>
							<?php echo wp_get_attachment_image($thumbnail_id, 'medium'); ?>
						</div>
					</div>
					<div class="right">
						<h2 class="title"><?php the_title(); ?></h2>
						<div class="item-description"><?php the_content(); ?></div>
						<!-- <a href="#" class="btn">Read more</a> -->
					</div>
				<?php else: ?>
					<div class="image">
						<?php $thumbnail_id = get_post_thumbnail_id(get_the_id()); ?>
						<?php echo wp_get_attachment_image($thumbnail_id, 'medium'); ?>
					</div>
					<h2 class="title"><?php the_title(); ?></h2>
					<?php if ($post_type == 'manuals'): ?>
						<p class="item-number">Item Number</p>
					<?php endif; ?>
					<?php if ($post_type == 'post' || $post_type == 'manuals'): ?>
						<p class="item-date"><?php the_time('F j, Y'); ?> </p>
					<?php endif; ?>
					<a href="<?php the_permalink(); ?>" class="btn">View <?php echo $post_type; ?></a>
				<?php endif; ?>
            </li>

       <?php endwhile; ?>
       <li class="no-results" aria-hidden="true">No results</li>
   </ul>
   <?php wp_reset_postdata(); ?>
<?php endif; ?>
