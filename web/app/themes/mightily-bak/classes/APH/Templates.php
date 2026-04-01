<?php
/**
 * Created by PhpStorm.
 * User: ntemple
 * Date: 2019-08-22
 * Time: 09:56
 */

namespace APH;

/**
 * Manage helpers for the templates and overrides.
 *
 * Class Templates
 * @package APH
 */
class Templates
{
    static function cart_preview_content() {
            // Changing the text for User Role: Teacher
        $orderText = "";
        if (is_user_role('teacher')) {
            $orderText = 'Proceed to Request';
        } else {
            $orderText = 'Checkout';
        }
        echo '<ul class="ajax-sub-menu sub-menu cart right">';

            global $woocommerce;
            $items = $woocommerce->cart->get_cart();

            foreach($items as $item) :

                $product =  wc_get_product( $item['data']->get_id());
                // $price = get_post_meta($item['product_id'] , '_price', true);
                $price = $product->get_price_html();

                $image = $product->get_image(); // accepts 2 arguments ( size, attr )


                echo '<li class="item item-' . $item['product_id'] . '">';
                    echo '<span>' . $image . '</span>';
                    echo '<span>';
                        echo '<div>' . $product->get_title() . '</div>';
                        echo '<div class="quantity"><span>Qty: </span><span class="quantity-total">' . $item['quantity'] . '</span></div>';
                        echo '<div class="price">' . $price . '</div>';
                    echo '</span>';
                echo '</li>';

            endforeach;

            echo '<li class="total">';
                echo '<span>Total: ' . $woocommerce->cart->get_cart_total() . '</span>';
            echo '</li>';
            echo '<li class="view-cart"><a href="/cart" class="btn white">View Cart</a></li>';
            echo '<li class="checkout"><a href="/checkout" class="btn white">' . $orderText . '</a></li>';

        echo '</ul>';
    }

    static function cart_preview_link(){
        global $woocommerce;
        $count = $woocommerce->cart->cart_contents_count;        
        echo '<a class="cart-preview-link" href="#">';
        echo '<span id="cart-counter" class="sub-text mobile-hidden-text">';
        echo sprintf( _n( '%d item', '%d items', $count), $count );
        echo '</span>';
        echo '<i class="fas fa-shopping-cart" aria-hidden="true"></i><span class="text mobile-hidden-text"> Cart</span>';
        echo '</a>';
    }

    static function add_admin_body_class($classes){
        global $post;
        $user_meta = get_userdata(get_current_user_id());
        $roles = $user_meta->roles;
        $roles = array_reverse($roles);
        $role = array_pop($roles);        
        $classes .= ' role-' . $role;

        // If this is an order post and the post status is auto draft, we need to add a class
        if($post && $post->post_type == 'shop_order' && $post->post_status == 'auto-draft'){
            $classes .= ' hide-line-items';
        }

        return $classes;
    }

    static function product_categories_hero(){
        $show_shop_hero = false;
        if(is_product_category()){
            $show_shop_hero = true;
            $shop_hero_bg = woocommerce_category_image();
        }
        if(is_shop()){
            $show_shop_hero = true;
            $shop_hero_bg = 'https://nyc3.digitaloceanspaces.com/aph/app/uploads/2018/10/26162035/MediaServices_6.jpg';
        }
        if($show_shop_hero){ ?>
            <section class="layout hero align-right category-hero" name="hero">
                <div class="wrapper">
                    <div class="row">
                        <div class="col">
                            <?php if (apply_filters( 'woocommerce_show_page_title', true)) : ?>
                                <h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
                            <?php endif; ?>
                            <?php do_action( 'woocommerce_archive_description'); ?>
                        </div>
                        <div class="image" style="background-image: url('<?php echo $shop_hero_bg; ?>');"></div>
                    </div>
                </div>
            </section>
        <?php }     
    }

    static function product_categories_list(){ ?>
        <?php if(is_shop() || is_product_category()) : ?>
        <div class="accordion-wrapper product-category-accordion-wrapper">
            <a class="skip-to-products" href="#main-search-results">Skip to products</a>
            <ul data-accordion class="bx--accordion tabs wc-tabs">
                <li data-accordion-item class="bx--accordion__item bx--accordion__item--active tab">
                    <button class="bx--accordion__heading h4" aria-expanded="false" aria-controls="pane1">
                        <h1 class="bx--accordion__title">Browse Categories</h1>
                    </button>
                    <div id="pane1" class="bx--accordion__content">
                        <div class="bx--accordion__content-wrapper">
                            <nav aria-label="Category Navigation">
                                <ul class="menu product-category-menu">
                                    <?php
                                        $cat_menu_args = [
                                            'menu' => 'category-menu',
                                            'container' => 'false',
                                            'items_wrap' => '%3$s'
                                        ];
                                    ?>
                                    <?php wp_nav_menu($cat_menu_args); ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </li>
            </ul>
        </div>  
        <?php endif; ?>     
    <?php }

    static function disable_shipping_calc_on_cart($show_shipping){
        if( is_cart() ) {
            return false;
        }
        return $show_shipping;
    }

    static function add_error_message_to_password_form($form){
        // No cookie, the user has not sent anything until now.
        if ( ! isset ( $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] ) )
            return $form;

        if(!wp_get_referer()){
            // Translate and escape.
            $msg = esc_html__( 'The password you entered was incorrect.', 'textdomain' );
            // We have a cookie, but it doesn’t match the password.
            $msg = "<p class='custom-password-message' style='color: red; font-weight: bold;'>$msg</p>";
        } else {
            $msg = '';
        }
    
        return $msg . $form;        
    }

    static function update_strings($translated, $untranslated, $domain){
        if (!is_admin() && $domain === 'woocommerce'){
            switch ($translated){
               case 'Cardholder Name (If Different)' :
                  $translated = 'Name on Card';
                  break;
            }
        }
        if(is_user_role('teacher') && $domain === 'woocommerce'){         
            switch ($translated){
                case 'Order #%1$s was placed on %2$s and is currently %3$s.' :
                   $translated = 'Request #%1$s was placed on %2$s and is currently %3$s.';
                   break;
                case 'Order updates' :
                    $translated = 'Request updates';
                    break;
                case 'Your order' :
                    $translated = 'Your request';
                    break;
                case 'Proceed to checkout' :
                    $translated = 'Proceed with Request';
                    break;
             }            
        }
        if(is_user_role('customer_service') && $domain === 'woocommerce'){         
            switch ($translated){
                case 'Customer payment page &rarr;' :
                   $translated = 'Open credit card page &rarr;';
                   break;
             }            
        }
        return $translated;
    }

    static function add_captcha_message_to_guest_checkout(){
        if(!is_user_logged_in() && get_field('captcha_message', 'option') && get_field('captcha_message', 'option') != ''){
            echo '<div class="woocommerce-captcha-message-wrapper">';
                echo get_field('captcha_message', 'option');
            echo '</div>';
        }
    }

    static function maybe_add_eua(){ ?>
        <?php if (isset($_GET['redirect_to']) && strpos($_GET['redirect_to'], 'oauth') !== false) : ?>
            <p class="form-row">
                <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__eua">
                    <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="eua" type="checkbox" id="eua" value="1"> <span><?php echo get_field('file_repo_agreement_link', 'option'); ?></span>
                </label>                 
            </p>
            <div id="eua-agreement" class="modal" aria-hidden="true">
                <div class="bg" tabindex="-1" data-micromodal-close>
                    <div class="layout basic-content dialog" role="dialog" aria-modal="true" aria-labelledby="eua-agreement-title" >
                        <header>
                            <p id="eua-agreement-title" class="h4"><?php echo get_field('file_repo_agreement_title', 'option'); ?></p>
                            <button type="button" class="close" aria-label="Close modal" data-micromodal-close></button>
                        </header>
                        <?php echo get_field('file_repo_agreement_content', 'option'); ?>
                    </div>
                </div>
            </div>                       
            <script>
                jQuery(document).ready(function(){

                    function add_eua_check_event(){
                        jQuery('form.woocommerce-form.woocommerce-form-login.login').on('submit', function(evt){
                            evt.preventDefault();
                            alert('You must agree to the EUA to use this website.');
                            return  false;
                        });                        
                    }

                    function remove_eua_check_event(){
                        jQuery('form.woocommerce-form.woocommerce-form-login.login').off('submit');
                    }

                    jQuery('input[name="eua"]').prop('checked', false);
                    jQuery('input[name="eua"]').attr('checked', false);

                    add_eua_check_event();
                    
                    jQuery('input[name="eua"]').on('change', function(){
                        if(jQuery(this).prop('checked')){
                            remove_eua_check_event();
                        } else {
                            add_eua_check_event();
                        }
                    });
                    
                });
            </script>
        <?php endif; ?>
    <?php }
    
    static function display_fq_accounts($current_user) {
        foreach (wp_get_terms_for_user($current_user, 'user-group') as $group) {
            $balance = get_field('fq_account_balance', 'user-group_' . $group->term_id);
            $outstanding = get_field('fq_outstanding_balance', 'user-group_' . $group->term_id);
            $available = get_field('fq_available_funding', 'user-group_' . $group->term_id);
            $overspent = get_field('fq_overspent', 'user-group_' . $group->term_id);

            echo '<div class="fq-accounts">';

            echo '<h3 class="h6 title">' . $group->name . '</h3>';

            echo '<ul style="margin-bottom: 15px;">';

            if (Roles::userHas([Roles::EOT, Roles::OOA], $current_user)) {

                echo '<li>Account Balance: ' . wc_price( $balance ) . '</li>';

                echo '<li>Outstanding Order Balance: ' . wc_price( $outstanding ) . '</li>';

                if (!Roles::userHas(Roles::NET, $current_user)) {

                    echo '<li><div class="label">Current Available Funding: </div><div class="balance h3">' . wc_price( $available ) . '</div></li>';

                    echo '<li>Overspent towards next fiscal year: ' . wc_price( $overspent ) . '</li>';

                    echo "<li><a href='#users-{$group->term_id}'>View Users Access</a></li>";
                
                }

            } elseif (Roles::userHas(Roles::TVI, $current_user)) {

                echo "<li><a href='#users-{$group->term_id}'>View Contact Info</a></li>";

            }
            echo '</ul>';

            echo '</div>';

        }
    }    

}
