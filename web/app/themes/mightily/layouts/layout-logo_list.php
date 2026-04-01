<section class="layout logo-list">
    <div class="content">
        <div class="wrapper">
            <?php if($layout_counter == 1) : ?>
                <h1 class='title h4'><?php echo get_sub_field('title'); ?></h1>
            <?php else : ?>
                <h2 class='title h4'><?php echo get_sub_field('title'); ?></h2>
            <?php endif; ?>
            <ul>
            	<?php while (have_rows('logos')): 
                    the_row(); 
                    $logo = get_sub_field('logo');
                    $url_img_src = get_sub_field('url_img_src');
                    $url_img_alt = get_sub_field('url_img_alt');
                    $link = get_sub_field('link');
                    $logo_src = '';
                    $logo_alt = '';
                    if($logo){
                        $logo_src = $logo['url'];
                        $logo_alt = $logo['alt'];
                    }
                    if($url_img_src && $url_img_src != ''){
                        $logo_src = $url_img_src;
                    }
                    if($url_img_alt && $url_img_alt != ''){
                        $logo_alt = $url_img_alt;
                    }

                ?>
                <li class="logo-item">
                    <?php if($link) : ?>
                        <a href="<?php echo $link; ?>" target="_self">
                            <img src="<?php echo $logo_src; ?>" alt="<?php echo $logo_alt; ?>" />
                        </a>
                    <?php else : ?>
                        <img src="<?php echo $logo_src; ?>" alt="<?php echo $logo_alt; ?>" />
                    <?php endif; ?>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
</section>
