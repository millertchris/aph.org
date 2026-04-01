<?php
/**
 * Created by PhpStorm.
 * User: ntemple
 * Date: 2019-08-29
 * Time: 10:41
 */

namespace APH;


class Roles {

    // Let the IDE check these for us. no more mis-spelling asistant.
    const EOT = 'eot';
    const OOA = 'eot-assistant';
    const TVI = 'teacher';
    const OPS = 'operations_admin';
    const NET = 'net';
    const ADM = 'administrator';
    const CSR = 'copy_of_csr';

    const FQARoles = [self::EOT, self::OOA, self::TVI];

    static function userHas($roles, \WP_User $user) {
        // Accept either a single role or an array of roles
        if(is_scalar($roles)) $roles = [ $roles ];

        if (! empty (array_intersect($roles, $user->roles))) {
            return true;
        }

        return false;
    }

    /**
     * Checks to see if a user is a member of the group of users
     * with FQA priviliges.
     *
     * If no user is passed, then the current user is ued.
     *
     *
     * @param \WP_User|null $user
     * @return bool
     */
    static function isFQAUser(\WP_User $user = null) {
        if (empty($user)) $user = wp_get_current_user();

        return self::userHas(self::FQARoles, $user);

    }

    static function accept_eot_invitation() {
        if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == '/accept-eot-invitation' || parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == '/wp/accept-eot-invitation') {
            if(isset($_GET['group'])){
                // If user is logged in, go ahead and assign then the new role
                if(is_user_logged_in()){                   
                    $group_ids = \APH\Encrypter::decryptString($_GET['group']);
                    $group_ids = explode('||', $group_ids);
                    $group_slug_array = [];
                    foreach($group_ids as $group_id){
                        $group_object = get_term($group_id, 'user-group');
                        // $group_slug = $group_object->slug;
                        $group_slug_array[] = $group_object->slug;
                        // Add the customer to the EOTs group
                    }
                    $current_user = wp_get_current_user();
                    $current_user_id = $current_user->ID;                    
                    wp_set_object_terms($current_user->ID, $group_slug_array, 'user-group');
                    $current_user->set_role('teacher');
                    wp_redirect(site_url() . '/profile?invite_success=true');
                    exit;   
                } else {
                    wp_redirect(site_url() . '/my-account?redirect_to=' . urlencode(site_url() . $_SERVER['REQUEST_URI']));
                    exit;                      
                }
            }
        } 

    }    

}