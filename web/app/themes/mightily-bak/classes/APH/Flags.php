<?php
/**
 * Created by PhpStorm.
 * User: ntemple
 * Date: 2019-09-23
 * Time: 14:02
 */

namespace APH;


/**
 * Class Flags
 * @package APH
 *
 * List of features to enable / disable. Could / should be environment
 * specific.
 *
 * Could be cool to add something like this to Mission Control to
 * enable and disable features from the UI
 *
 * Note: always make these positive, so if the variable is not there,
 * the feature is disabled by default.
 */

class Flags
{
   static $ENABLE_QUOTA_USERS = true;
}

