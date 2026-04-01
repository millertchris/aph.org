<?php get_header(); ?>

<?php
    $author = get_field('author');
?>

    <section class="the-post">
        <div class="wrapper">
            <?php while ( have_posts() ) : the_post(); ?>
                <h1 class="title">
                    <span class="featured-image">
                        <?php $thumbnail_id = get_post_thumbnail_id( get_the_id() ); ?>
                        <?php echo wp_get_attachment_image( $thumbnail_id, 'original' ); ?>
                    </span>
                    <?php the_title(); ?>
                </h1>
                <div class="article-info">
                    <?php if ($author && is_array($author)): ?>
                        <?php $author_string = ''; ?>
                        <?php foreach($author as $single_author) : ?>
                            <?php $author_string .= get_field('first_name', $single_author->ID) . ' ' . get_field('last_name', $single_author->ID) . ', '; ?>
                        <?php endforeach; ?>
                        <?php if(count($author) == 1) : ?>
                            <?php $author_label = 'Author: '; ?>
                        <?php else : ?>
                            <?php $author_label = 'Authors: '; ?>
                        <?php endif; ?>
                        <?php echo '<span class="author-names">' . $author_label . ' ' . substr_replace($author_string, '', -2, 1) . '</span>'; ?>
                    <?php endif; ?>
                    <span class="date"><?php the_time('F j, Y'); ?></span>
                </div>
                <div class="content clearfix">
                    <?php the_content(); ?>
                </div>


                <?php
                    $next_post_url = '';
                    $previous_post_url = '';
                    if (get_adjacent_post(false,'',false)) {
                        $next_post_url = get_permalink( get_adjacent_post(false,'',false)->ID );
                    }
                    if (get_adjacent_post(false,'',true)) {
                        $previous_post_url = get_permalink( get_adjacent_post(false,'',true)->ID );
                    }
                ?>

                <div class="post-nav">
                    <div class="row">
                        <div class="col">
                            <?php if ($previous_post_url): ?>
                                <a href="<?php echo $previous_post_url; ?>" class="btn black">Previous Article</a>
                            <?php endif; ?>
                        </div>
                        <div class="col">
                            <?php if ($next_post_url): ?>
                                <a href="<?php echo $next_post_url; ?>" class="btn black">Next Article</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="post-sharing">
                    <h2 class="h4">Share this article.</h2>
                    <ul class="social">
        				<li><a aria-label="Share on Facebook" target="_blank" href="<?php echo 'https://www.facebook.com/sharer/sharer.php?u='.get_the_permalink();?>"><i class="fab fa-facebook-f" aria-hidden="true"></i></a></li>
        				<li><a aria-label="Share on Twitter" target="_blank" href="<?php echo 'https://twitter.com/intent/tweet?text='.urlencode('Read '.get_the_title().' - ').get_the_permalink(); ?>"><i class="fab fa-twitter" aria-hidden="true"></i></a></li>
                        <li><a aria-label="Share on Linked In" target="_blank" href="<?php echo 'https://www.linkedin.com/shareArticle?mini=true&url='.get_the_permalink().'&title='.urlencode(get_the_title()); ?>"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a></li>
        			</ul>
                </div>

            <?php endwhile; ?>

        </div>
    </section>

    <?php if ($author): ?>
        <!-- <section class="about-author">
            <div class="wrapper">
                <div class="row">
                    <div class="col image">
                        <?php $thumbnail = get_field('image', $author->ID); ?>
                        <?php //var_dump($thumbnail); ?>
                        <?php $thumbnail_id = $thumbnail['ID']; ?>
                        <?php echo wp_get_attachment_image( $thumbnail_id, 'thumbnail' ); ?>
                    </div>
                    <div class="col">
                        <h3 class="h4 title">About the author</h3>
                        <p class="author-name"><?php the_field('first_name', $author->ID); ?> <?php the_field('last_name', $author->ID); ?>, <?php the_field('position', $author->ID); ?></p>
                        <p><?php echo get_post_field('post_content', $author->ID); ?></p>
                    </div>
                </div>
            </div>
        </section> -->
    <?php endif; ?>

        <?php
        //for use in the loop, list 3 post titles related to first tag on current post
        $tags = wp_get_post_tags($post->ID);

        if ($tags) :
            $first_tag = $tags[0]->term_id;
            $args = array(
                'tag__in'             => array($first_tag),
                'post__not_in'        => array($post->ID),
                'posts_per_page'      => 3,
                'ignore_sticky_posts' => 1
            );
            $the_query = new WP_Query($args);

            if( $the_query->have_posts() ) : ?>

                <section class="layout listing grid cards medium">
                    <div class="intro">
                        <div class="wrapper">
                            <h2>Related articles</h2>
                        </div>
                    </div>
                    <div class="wrapper">
                        <?php include(locate_template('layouts/views/grid-item.php')); ?>
                    </div>
                </section>

            <?php endif; ?>

            <?php wp_reset_query(); ?>
            <?php endif; ?>

<?php get_footer(); ?>
