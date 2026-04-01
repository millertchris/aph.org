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
          <?php
            $institute = get_field('institute');
            $position = get_field('position');
            $street_address = get_field('street_address');
            $city = get_field('city');
            $state = get_field('state');
            $zip = get_field('zip_code');
            $phone = get_field('phone');
            $fax = get_field('fax');
            $email = get_field('email');
            $quota_account = get_field('quota_account');
            $state_rep = get_field('state_represented');

            $thumbnail_id = get_post_thumbnail_id(get_the_id());
            $content = get_the_content();

           ?>

          <?php if ($thumbnail_id) : ?>
            <div class="left">
              <div class="image">
                <?php echo wp_get_attachment_image($thumbnail_id, 'medium'); ?>
              </div>
            </div>
          <?php endif; ?>
          <div class="right">
            <h2 class="title h3"><?php the_title(); ?></h2>
            <div class="people-info">
              <div class="people-name">
                <?php if ($state_rep): ?>
                  <p class="state-rep"><?php echo $state_rep; ?></p>
                <?php endif; ?>
                <?php if ($quota_account): ?>
                  <p class="quota-account"><?php echo $quota_account; ?></p>
                <?php endif; ?>
                <?php if ($institute): ?>
                  <p class="institute"><?php echo $institute; ?></p>
                <?php endif; ?>
                <?php if ($position): ?>
                  <p class="position"><?php echo $position; ?></p>
                <?php endif; ?>
              </div>
              <?php if ($street_address || $city || $state || $zip) : ?>
                <div class="address">
                  <p class="people-section-title">Address</p>
                  <p>
                    <?php if ($street_address) : ?>
                      <span class="street-address"><?php echo $street_address; ?></span><br>
                    <?php endif; ?>
                    <?php if ($city && $state && $zip) : ?>
                      <span class="city-state"><?php echo $city; ?>, <?php echo $state; ?></span><span class="zip-code"> <?php echo $zip; ?></span>
                    <?php endif; ?>
                  </p>
                </div>
              <?php endif; ?>
              <?php if ($phone || $fax || $email) : ?>
                <div class="contact">
                <p class="people-section-title">Contact</p>
                <?php if ($phone) : ?>
                  <p class="phone"><i class="fas fa-phone" aria-hidden="true"></i> <?php echo $phone; ?></p>
                <?php endif; ?>
                <?php if ($fax) : ?>
                  <p class="fax"><i class="fas fa-fax" aria-hidden="true"></i> <?php echo $fax; ?></p>
                <?php endif; ?>
                <?php if ($email) : ?>
                  <p class="email"><i class="fas fa-envelope" aria-hidden="true"></i> <?php echo $email; ?></p>
                <?php endif; ?>
              </div>
            <?php endif; ?>
            </div>
            <?php if ($content) : ?>
              <div class="people-content">
                <?php the_content(); ?>
              </div>
            <?php endif; ?>
          </div>

  				<?php else: ?>
            <div class="image">
            <?php $thumbnail_id = get_post_thumbnail_id(get_the_id()); ?>
            <?php echo wp_get_attachment_image($thumbnail_id, 'medium'); ?>
            </div>
            <a href="<?php the_permalink(); ?>" class="list-item-link">
              <h2 class="title"><?php the_title(); ?></h2>

              <?php if ($post_type == 'manuals'): ?>
                <p class="item-number">Item Number</p>
              <?php endif; ?>

              <?php if ($post_type == 'post' || $post_type == 'manuals'): ?>
                <p class="item-date"><?php the_time('F j, Y'); ?> </p>
              <?php endif; ?>

              <?php if ($post_type == 'custom') : ?>
                <span class="btn" aria-hidden="true">View</span>
              <?php else : ?>
                <span class="btn" aria-hidden="true">View <?php echo $post_type; ?></span>
              <?php endif; ?>
            </a>

  			<?php endif; ?>

        </li>

       <?php endwhile; ?>
       <li class="no-results" aria-hidden="true">No results</li>
   </ul>
   <?php wp_reset_postdata(); ?>
<?php endif; ?>
