<?php
    $template_url = get_template_directory_uri();
    $site_url = get_site_url();
?>

<?php if (is_woocommerce()): ?>
</div> <!-- Closing wrapper -->
<?php endif; ?>

</main>


<footer class="footer">
	<?php
        function breadcrumbs() {
            if (is_page_template('default') && !is_page(1338)) { // Page 1338 is the home page
                echo '<div class="wrapper">';
                echo '<nav class="breadcrumbs" aria-label="Breadcrumb Navigation">';
                $object = get_queried_object();
                $post_parent = get_post($object->post_parent);

                if ($object->post_parent != 0) {
                    echo '<span><a href="' . home_url() . '">Home</a></span>';
                    echo '<span><a href="' . get_the_permalink($post_parent->ID) . '">' . $post_parent->post_title . '</a></span>';
                    echo '<span><a href="' . get_the_permalink($object->ID) . '">' . $object->post_title . '</a></span>';
                } else {
                    echo '<span class="breadcrumb-logo"><a href="' . home_url() . '">Home</a></span>';
                    echo '<span>' . $object->post_title . '</span>';
                }
                echo '</nav>';
                echo '</div>';
            }
        }
    ?>
    <?php
      if (is_woocommerce()) {
          echo '<div class="wrapper">';
          woocommerce_breadcrumb();
          echo '</div>';
      } else {
          breadcrumbs();
      }
     ?>
    <div class="main-footer">
        <div class="wrapper">
            <nav aria-label="Footer Navigation">
                <ul class="menu">
                    <?php
                        $args = [
                            'menu' => 'footer-menu',
                            'container' => 'false',
                            'items_wrap' => '%3$s'
                        ];
                    ?>
                    <?php wp_nav_menu($args); ?>
                </ul>
            </nav>
            <div class="logo-social">
                <a href="<?php echo esc_url(home_url('/')); ?>"><img class="logo" src="<?php echo $template_url; ?>/app/assets/img/logo-black.svg" alt="American Printing House for the Blind Home Page"></a>
                <p class="tagline">Welcome everyone</p>

                <?php if (have_rows('footer_social', 'option')): ?>

                    <ul class="social">

                    <?php while (have_rows('footer_social', 'option')): the_row(); ?>

                        <?php
                            $network = get_sub_field('network');
                            $link = get_sub_field('link');
                            $link_url = $link['url'];
                            $link_title = $link['title'];
                            $link_target = $link['target'];

                            if ($link_target == null) {
                                $link_target = '_self';
                            }
                        ?>

                        <li class="social-item">
                            <?php if ($network == 'facebook'): ?>
                                <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="Find us on Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                            <?php elseif ($network == 'twitter'): ?>
                                <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="Tweet us on Twitter"><i class="fab fa-twitter" aria-hidden="true"></i></a>
                            <?php elseif ($network == 'instagram'): ?>
                                <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="We’re on Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                            <?php elseif ($network == 'snapchat'): ?>
                                <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="Connect with us on Snapchat"><i class="fab fa-snapchat-ghost" aria-hidden="true"></i></a>
                            <?php elseif ($network == 'pinterest'): ?>
                                <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="Share Pins on Pinterest"><i class="fab fa-pinterest-p" aria-hidden="true"></i></a>
                            <?php elseif ($network == 'linkedin'): ?>
                                <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="Connect with us on Linked In"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
                            <?php elseif ($network == 'youtube'): ?>
                                <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="Visit our YouTube channel"><i class="fab fa-youtube" aria-hidden="true"></i></a>
                            <?php elseif ($network == 'vimeo'): ?>
                                <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="Visit our Vimeo"><i class="fab fa-vimeo-v" aria-hidden="true"></i></a>
                            <?php endif; ?>
                        </li>

                    <?php endwhile; ?>

                    </ul>

                <?php endif; ?>

                <!--<a target="_self" class="mightily-logo mightily-home" href="https://mightily.com/"><img class="logo mightily" src="<?php echo $template_url; ?>/app/assets/img/Mightily.jpg" alt="Mightily Home Page Link"></a>-->
                
            </div>
        </div>
    </div>
    <div class="sub-footer">
        <div class="copyright"><?php the_field('copyright', 'option'); ?></div>
        <nav aria-label="Sub-footer Navigation">
            <ul class="sub-footer-menu">
                <?php
                    $args = [
                        'menu' => 'subfooter-menu',
                        'container' => 'false',
                        'items_wrap' => '%3$s'
                    ];
                ?>
                <?php wp_nav_menu($args); ?>
            </ul>
        </nav>
    </div>

</footer>

<script src="https://unpkg.com/@ungap/url-search-params"></script>

<?php wp_footer(); ?>
<div class="media-check"></div>
<div hidden>
    <span id="new-window-message">Opens in a new window</span>
</div>
<script type="text/javascript">
    /*<![CDATA[*/
    (function() {
    var sz = document.createElement('script'); sz.type = 'text/javascript'; sz.async = true;
    sz.src = '//siteimproveanalytics.com/js/siteanalyze_6023927.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(sz, s);
    })();
    /*]]>*/
</script>

</body>
</html>
