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
 * Class OrderHistory
 * @package APH
 */
class OrderHistory
{
    static function get_my_orders($params = null){
        if(isset($params['user'])){
            $current_user = $params['user'];
        } else {
            $current_user = wp_get_current_user();
        }
        if(isset($params['date_created'])){
            $date_created = $params['date_created'];
        } else {
            $date_created = false;
        }
        $args = array(
            'orderby' => 'date',
            'order' => 'DESC',
            // 'posts_per_page' => 150,
            'limit' => 150, // APH-515, we'll need paging.
            'customer_id' => $current_user->ID
        );
        if($date_created){
            $args['date_created'] = $date_created;
        }
        $query = new \WC_Order_Query($args);
        return $query->get_orders();     
    }

    static function get_teacher_orders($params = null){
        if(isset($params['user'])){
            $current_user = $params['user'];
        } else {
            $current_user = wp_get_current_user();
        }
        if(isset($params['date_created'])){
            $date_created = $params['date_created'];
        } else {
            $date_created = false;
        }              
        $teacher_orders = false;
        // If EOT or Assistant we need to get the orders from teachers that are in the same group as current user
        if (in_array('eot', $current_user->roles) || in_array('eot-assistant', $current_user->roles)) {
    
            // Create array where we will store child user ids
            $teacher_users = [];
            $teacher_orders = [];
    
            // Loop through the groups of the user
            foreach(wp_get_terms_for_user($current_user, 'user-group') as $group){
    
                // Loop through the users of this group and append them to the child array
                foreach(get_objects_in_term($group->term_id, 'user-group') as $child_user_id){
    
                    $child_user_object = get_userdata($child_user_id);
    
                    if(in_array('teacher', $child_user_object->roles)){
    
                        $teacher_users[] = $child_user_object;
    
                    }
    
                }
    
            }
    
            // print_r($teacher_users);
            // If there are teachers in the same group as this EOT then we will get their orders
            if(!empty($teacher_users)){
                
                $teacher_users = array_unique($teacher_users, SORT_REGULAR);
                    
                // For each teacher we need to get their orders that used eot payment option
                foreach($teacher_users as $teacher_user){
    
                    $args = array(
                        'customer_id' => $teacher_user->ID,
                        'limit' => 150
                        //'payment_method' => 'eot_gateway'
                    );
                    if($date_created){
                        $args['date_created'] = $date_created;
                    }    
                    foreach(wc_get_orders($args) as $order){
    
                        $teacher_orders[] = $order;
    
                    }
    
                }
            }
    
        }
        return $teacher_orders;
    }

    static function get_eot_ooa_orders($params = null){
        if(isset($params['user'])){
            $current_user = $params['user'];
        } else {
            $current_user = wp_get_current_user();
        }
        if(isset($params['date_created'])){
            $date_created = $params['date_created'];
        } else {
            $date_created = false;
        }              
        $eot_ooa_orders = false;
        // If EOT or Assistant we need to get the orders from other eots and ooas that are in the same group as current user
        if (in_array('eot', $current_user->roles) || in_array('eot-assistant', $current_user->roles)) {
    
            // Create array where we will store child user ids
            $eot_ooa_users = [];
            $eot_ooa_orders = [];
    
            // Loop through the groups of the user
            foreach(wp_get_terms_for_user($current_user, 'user-group') as $group){
                // Loop through the users of this group and append them to the child array
                foreach(get_objects_in_term($group->term_id, 'user-group') as $child_user_id){
                    // Make sure we don't get our own orders again since our user id will be in this list
                    if($current_user->ID != $child_user_id){
                        $child_user_object = get_userdata($child_user_id);
                        if(in_array('eot', $child_user_object->roles) || in_array('eot-assistant', $child_user_object->roles)){
                            $eot_ooa_users[] = $child_user_object;
                        }
                    }
                }
            }
    
            // print_r($eot_ooa_users);
            // If there are eot or ooa in the same group as this EOT then we will get their orders
            if(!empty($eot_ooa_users)){
                
                $eot_ooa_users = array_unique($eot_ooa_users, SORT_REGULAR);
                    
                // For each eot or ooa we need to get their orders that used eot payment option
                foreach($eot_ooa_users as $eot_ooa_user){
    
                    $args = array(
                        'customer_id' => $eot_ooa_user->ID,
                        'limit' => 150
                        //'payment_method' => 'eot_gateway'
                    );
                    if($date_created){
                        $args['date_created'] = $date_created;
                    }
                    foreach(wc_get_orders($args) as $order){
    
                        $eot_ooa_orders[] = $order;
    
                    }
    
                }
            }
    
        }
        return $eot_ooa_orders;        
    }

}
