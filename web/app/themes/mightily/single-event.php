<?php get_header(); ?>

<section class="the-post">
    <div class="wrapper">
        <?php while (have_posts()) : the_post(); ?>
            <div class="intro-info">
                <h1 class="title"><?php the_title(); ?></h1>
                <span class="featured-image">
                    <?php $thumbnail_id = get_post_thumbnail_id(get_the_id()); ?>
                    <?php echo wp_get_attachment_image($thumbnail_id, 'original'); ?>
                </span>
            </div>
            <div class="event-info">
                <div class="occurrences">
                    <?php if (have_rows("occurrences")) : ?>
                        <h2>Dates</h2>
                        <?php while (have_rows("occurrences")) {
                            $occurrence_row = the_row(true);
                            APH\Templates::event_date_html($occurrence_row);
                        }
                        ?>
                    <?php endif; ?>
                </div>
                <?php
                $location = get_field('location');
                if ($location) :
                ?>
                    <div class="location">
                        <h2>Location</h2>
                        <?php echo $location; ?>
                    </div>
                <?php endif; ?>
                <?php
                $description = get_field('description');
                if ($description) :
                ?>
                    <div class="description">
                        <h2>Description</h2>
                        <?php echo $description; ?>
                    </div>
                <?php endif; ?>
                <?php
                $sessions = get_field('sessions');
                if ($sessions) :
                ?>
                    <div class="sessions">
                        <h2>Sessions</h2>
                        <?php echo $sessions; ?>
                    </div>
                <?php endif; ?>
                <?php
                $registration = get_field('rgsl');
                $registration_url = '';
                $registration_title = '';
                $registration_target = '_self';
                $registration_a11y = '';
                $registration_icon = '';

                if ($registration && is_array($registration)) {
                    $registration_url = $registration['url'] ?? '';
                    $registration_title = $registration['title'] ?? '';
                    $registration_target = $registration['target'] ?? '_self';

                    if ($registration_target == '_blank') {
                        $registration_a11y = ' rel="noopener" aria-describedby="new-window-message"';
                        $registration_icon = ' <i aria-hidden="true" class="fas fa-external-link-alt"></i>';
                    }
                }

                $alt_registration = get_field('alt_rgsl');
                $alt_registration_url = '';
                $alt_registration_title = '';
                $alt_registration_target = '_self';
                $alt_registration_a11y = '';
                $alt_registration_icon = '';

                if ($alt_registration && is_array($alt_registration)) {
                    $alt_registration_url = $alt_registration['url'] ?? '';
                    $alt_registration_title = $alt_registration['title'] ?? '';
                    $alt_registration_target = $alt_registration['target'] ?? '_self';

                    if ($alt_registration_target == '_blank') {
                        $alt_registration_a11y = ' rel="noopener" aria-describedby="new-window-message"';
                        $alt_registration_icon = ' <i aria-hidden="true" class="fas fa-external-link-alt"></i>';
                    }
                }
                ?>
                <?php if ($registration || $alt_registration) : ?>
                    <div class="registration">
                        <div class="buttons">
                            <?php echo ($registration) ? '<a href="' . $registration_url . '" target="' . $registration_target . '" class="btn"' . $registration_a11y . '>' . $registration_title . $registration_icon . '</a>' : ''; ?>
                            <?php echo ($alt_registration) ? '<a href="' . $alt_registration_url . '" target="' . $alt_registration_target . '" class="btn"' . $alt_registration_a11y . '>' . $alt_registration_title . $alt_registration_icon . '</a>' : ''; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php
                $exhibit_hours = get_field('exhibit_hours');
                if ($exhibit_hours) :
                ?>
                    <div class="exhibit-hours">
                        <h2>Exhibit Hours</h2>
                        <?php echo $exhibit_hours; ?>
                    </div>
                <?php endif; ?>
                <?php if (have_rows('presenters')) : ?>
                    <div class="presenters">
                        <h2>Presenters</h2>
                        <ul>
                            <?php while (have_rows('presenters')) : the_row(); ?>
                                <li>
                                    <?php echo get_sub_field('presenter'); ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php
                $primary_core_or_ecc_area = get_field('primary_core_or_ecc_area');
                if ($primary_core_or_ecc_area && isset($primary_core_or_ecc_area[0]) && $primary_core_or_ecc_area[0] != '') :
                ?>
                    <div class="primary-core-or-ecc-area">
                        <h2>Primary Core or ECC Area</h2>
                        <?php echo implode(', ', $primary_core_or_ecc_area); ?>
                    </div>
                <?php endif; ?>
                <?php
                $target_audience = get_field('target_audience');
                if ($target_audience && isset($target_audience[0]) && $target_audience[0] != '') :
                ?>
                    <div class="target-audience">
                        <h2>Target Audience</h2>
                        <?php echo implode(', ', $target_audience); ?>
                    </div>
                <?php endif; ?>
                <?php
                $pre_requisite_knowledge = get_field('pre-requisite_knowledge');
                if ($pre_requisite_knowledge) :
                ?>
                    <div class="pre-requisite-knowledge">
                        <h2>Pre-requisite Knowledge</h2>
                        <?php echo $pre_requisite_knowledge; ?>
                    </div>
                <?php endif; ?>
                <?php
                $lesson_plan_goal = get_field('lesson_plan_goal');
                if ($lesson_plan_goal) :
                ?>
                    <div class="lesson-plan-goal">
                        <h2>Lesson Plan Goal</h2>
                        <?php echo $lesson_plan_goal; ?>
                    </div>
                <?php endif; ?>
                <?php
                $learning_objectives = get_field('learning_objectives');
                if ($learning_objectives) :
                ?>
                    <div class="learning-objectives">
                        <h2>Learning Objectives</h2>
                        <?php echo $learning_objectives; ?>
                    </div>
                <?php endif; ?>
                <?php
                $materials_needed = get_field('materials_needed');
                if ($materials_needed) :
                ?>
                    <div class="materials-needed">
                        <h2>Materials Needed</h2>
                        <?php echo $materials_needed; ?>
                    </div>
                <?php endif; ?>
                <?php if (have_rows('resources')) : ?>
                    <div class="resources">
                        <h2>Resources</h2>
                        <ol>
                            <?php while (have_rows('resources')) :
                                the_row();
                                $file = get_sub_field('resource_item');
                                $file_url = '';
                                $file_title = '';
                                if ($file && is_array($file)) {
                                    $file_url = $file['url'] ?? '';
                                    $file_title = $file['title'] ?? '';
                                }
                            ?>
                                <?php if ($file_url && $file_title) : ?>
                                    <li>
                                        <a href="<?php echo $file_url; ?>"><?php echo $file_title; ?> (<?php echo $file && is_array($file) ? getFileExtension($file) : ''; ?>)</a>
                                    </li>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        </ol>
                    </div>
                <?php endif; ?>
                <?php
                $external_link = get_field('external_link');
                $external_link_url = '';
                $external_link_title = '';
                $external_link_target = '_self';
                $external_link_a11y = '';
                $external_link_icon = '';

                if ($external_link && is_array($external_link)) {
                    $external_link_url = $external_link['url'] ?? '';
                    $external_link_title = $external_link['title'] ?? '';
                    $external_link_target = $external_link['target'] ?? '_self';

                    if ($external_link_target == '_blank') {
                        $external_link_a11y = ' rel="noopener" aria-describedby="new-window-message"';
                        $external_link_icon = ' <i aria-hidden="true" class="fas fa-external-link-alt"></i>';
                    }
                }

                $survey_link = get_field('survey_link');
                $survey_link_url = '';
                $survey_link_title = '';
                $survey_link_target = '_self';
                $survey_link_a11y = '';
                $survey_link_icon = '';

                if ($survey_link && is_array($survey_link)) {
                    $survey_link_url = $survey_link['url'] ?? '';
                    $survey_link_title = $survey_link['title'] ?? '';
                    $survey_link_target = $survey_link['target'] ?? '_self';

                    if ($survey_link_target == '_blank') {
                        $survey_link_a11y = ' rel="noopener" aria-describedby="new-window-message"';
                        $survey_link_icon = ' <i aria-hidden="true" class="fas fa-external-link-alt"></i>';
                    }
                }
                ?>
                <?php if ($external_link || $survey_link) : ?>
                    <div class="external-survey-link">
                        <div class="buttons">
                            <?php echo ($external_link) ? '<a href="' . $external_link_url . '" target="' . $external_link_target . '" class="btn"' . $external_link_a11y . '>' . $external_link_title . $external_link_icon . '</a>' : ''; ?>
                            <?php echo ($survey_link) ? '<a href="' . $survey_link_url . '" target="' . $survey_link_target . '" class="btn"' . $survey_link_a11y . '>' . $survey_link_title . $survey_link_icon . '</a>' : ''; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="post-sharing">
                <h2>Share this event.</h2>
                <ul class="social">
                    <li><a aria-label="Share on Facebook" target="_blank" href="<?php echo 'https://www.facebook.com/sharer/sharer.php?u=' . get_the_permalink(); ?>"><i class="fab fa-facebook-f" aria-hidden="true"></i></a></li>
                    <li><a aria-label="Share on Twitter" target="_blank" href="<?php echo 'https://twitter.com/intent/tweet?text=' . urlencode('Read ' . get_the_title() . ' - ') . get_the_permalink(); ?>"><i class="fab fa-twitter" aria-hidden="true"></i></a></li>
                    <li><a aria-label="Share on Linked In" target="_blank" href="<?php echo 'https://www.linkedin.com/shareArticle?mini=true&url=' . get_the_permalink() . '&title=' . urlencode(get_the_title()); ?>"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a></li>
                </ul>
            </div>

        <?php endwhile; ?>

    </div>
</section>

<?php
if (get_field('show_listing_layout')) {
    while (have_rows('listing')) {
        the_row();
        include(locate_template('layouts/layout-listing.php'));
    }
}
//commit
?>
<?php get_footer(); ?>