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
    const NET = 'net_30';
    const ADM = 'administrator';

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

}