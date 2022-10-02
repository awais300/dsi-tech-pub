<?php

namespace DSI\TechPub;

use DSI\TechPub\User\UserMeta;
use DSI\TechPub\User\UserLogin;


defined('ABSPATH') || exit;

/**
 * Class Test
 * @package DSI\TechPub
 */

class Test
{
    /**
     * Construct the plugin.
     */
    public function __construct()
    {
        add_action('init', array($this, 'add_test_data'));
    }

    public function add_test_data()
    {
        if (!is_user_logged_in()) {
            return;
        }

        $user_id = get_current_user_id();
        (UserMeta::get_instance())->save_user_meta($user_id, UserMeta::META_TECH_PUB_ACCESS_ALLOWED, UserLogin::REGISTER_STATUS_PENDING);
        (UserMeta::get_instance())->save_user_meta($user_id, UserMeta::META_TECH_PUB_ACCESS_EXPIRATION_DATE, '');
    }
}
