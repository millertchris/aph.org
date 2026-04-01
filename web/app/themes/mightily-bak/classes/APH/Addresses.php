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
class Addresses
{
    static function is_address_enabled(){
        if(is_page_template('template/addresses.php') || is_checkout()){
            $current_user = wp_get_current_user();
            if (Roles::userHas([Roles::TVI, Roles::EOT, Roles::OOA, Roles::ADM], $current_user) && !Roles::userHas(Roles::NET, $current_user)){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    static function get_address_post_id(){
        $post_id = 0;
        $args = array(
            'author' => get_current_user_id(),
            'post_type' => 'addresses',
            'post_status' => 'private',
            'posts_per_page' => 1
        );
        $query = new \WP_Query($args); 
        if($query->post_count == 1){
            $post_id = $query->posts[0]->ID;
        }
        wp_reset_postdata();
        return $post_id;
    }

    static function get_address_data(){
        $post_id = self::get_address_post_id();
        if($post_id == 0){
            $sample_data = array(
                array(
                    "group" => "Sample Group",
                    "fq_account" => "FQ 123",
                    "first_name" => "Jane",
                    "last_name" => "Doe",
                    "company" => "Company Name",
                    "street_address_1" => "123 Any St",
                    "street_address_2" => "Suite 123",
                    "city" => "Anytown",
                    "state" => "AL",
                    "postal_code" => "12345",
                    "country" => "US",
                    "phone" => "123-456-7890",
                    "id" => 1
                )                
            );
            return json_encode($sample_data);
        } else {
            return get_post_field('post_content', self::get_address_post_id());
        }
    }

    static function addresses(){
        check_ajax_referer( 'addresses-nonce', 'security' );
        // var_dump($_REQUEST['address_data']);
        // Query Address posts that this user authors. If a post is returned, use it. If not, then insert post.
        $post_id = self::get_address_post_id();
        $post = wp_insert_post([
            'ID' => $post_id,
            'post_status' => 'private',
            'post_type' => 'addresses',
            'post_content' => $_REQUEST['address_data']
        ]);
        die();
    }

    static function addresses_ajax_enqueue(){
        if(!is_user_logged_in()){
            return false;
        }
        if(!self::is_address_enabled()){
            return false;
        }
        // Enqueue javascript on the frontend.
        wp_enqueue_script(
            'addresses-ajax-script',
            get_template_directory_uri() . '/app/assets/js/manageAddresses.js',
            ['jquery'],
            '6.2.4'
        );
        $supplemental_data = array(
            'l10n_print_after' => json_decode ( html_entity_decode(self::get_address_data(), ENT_NOQUOTES) )
        );
        $countries = WC()->countries->get_shipping_countries();
        $states = WC()->countries->get_shipping_country_states();
        // The wp_localize_script allows us to output the ajax_url path for our script to use.
        wp_localize_script(
            'addresses-ajax-script',
            'addresses_ajax_obj',
            array(
                'internalData' => $supplemental_data,
                'countries' => $countries,
                'states' => $states,
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'addresses_ajax_nonce' => wp_create_nonce('addresses-nonce')
            )
        );
    }

    static function add_address_combobox(){ ?>
        <?php if (self::is_address_enabled()) : ?>
        <div class="carbon-app">
            <p style="font-weight: bold;">Ship to a different address</p>
            <div id="combo-app"></div>
        </div>
        <script type="text/x-template" id="combo-template">
            <cv-combo-box 
            :label="label"
            :title="title"
            :disabled="disabled"
            :auto-filter="autoFilter"
            :auto-highlight="autoHighlight"
            :value="initialValue"
            :options="options"
            @change="onChange"
            >
            </cv-combo-box>
        </script>
        <?php endif; ?>
    <?php }

}
