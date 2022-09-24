<?php

namespace DSI\TechPub\User;

use DSI\TechPub\TemplateLoader;
use DSI\TechPub\User\UserMeta;
use DSI\TechPub\User\UserRoles;

defined('ABSPATH') || exit;

/**
 * Class UserLogin
 * @package DSI\TechPub
 */

class UserLogin
{
    /**
     * The template loader.
     *
     * @var $loader
     */
    public $loader = null;

    /**
     * The register status key.
     *
     * @var REGISTER_STATUS
     */
    public const REGISTER_STATUS_KEY = 'register_status';


    /**
     * The register pending status.
     *
     * @var REGISTER_STATUS
     */
    public const REGISTER_STATUS_PENDING = 0;

    /**
     * The register active status.
     *
     * @var REGISTER_STATUS
     */
    public const REGISTER_STATUS_ACTIVE = 1;

    /**
     * The to Email address.
     *
     * @var TO_EMAIL
     */
    public const TO_EMAIL = 'Matthew.Starcher@dsi-hums.com';

    /**
     * The user account page.
     *
     * @var MY_ACCOUNT_PAGE
     */
    public const MY_ACCOUNT_PAGE = 'my-account';

    /**
     * Construct the plugin.
     */
    public function __construct()
    {
        $this->loader = TemplateLoader::get_instance();
        add_action('user_register', array($this, 'after_user_register'), 10, 2);
        add_action('woocommerce_register_form_end', array($this, 'register_message'));
        add_filter('wp_authenticate_user', array($this, 'authenticate_retail_user'));
    }

    /**
     * Set user info after successful registration.
     *
     * @param int $user_id
     * @param array $userdata
     */
    public function after_user_register($user_id, $userdata)
    {
        (UserMeta::get_instance())->save_user_meta($user_id, UserMeta::META_TECH_PUB_ACCESS_ALLOWED, self::REGISTER_STATUS_PENDING);
        (UserMeta::get_instance())->save_user_meta($user_id, UserMeta::META_TECH_PUB_ACCESS_EXPIRATION_DATE, '');
        (UserRoles::get_instance())->set_user_role($user_id, UserRoles::ROLE_DSI_RETAIL);

        $this->new_user_admin_notification($userdata);

        wp_logout();
        wp_redirect('/' . self::MY_ACCOUNT_PAGE . '/?' . self::REGISTER_STATUS_KEY . '=pending');
        exit;
    }

    /**
     * Display registration notice.
     */
    public function register_message()
    {
        $data = array();
        $this->loader->get_template(
            'user-register-form.php',
            $data,
            DSI_CUST_PLUGIN_DIR_PATH . '/templates/woocommerce/',
            true
        );
    }

    /**
     * Send admin email notification when new user register on site.
     * @param array $userdata
     */
    public function new_user_admin_notification($userdata)
    {

        $data = array(
            'username' => $userdata['user_login'],
            'email' => $userdata['user_email'],
        );

        $to = get_option('admin_email');
        if (empty($to)) {
            $to = self::TO_EMAIL;
        }

        $subject = 'New Retail customer';
        $message = $this->loader->get_template(
            'user-register-admin-notification.php',
            $data,
            DSI_CUST_PLUGIN_DIR_PATH . '/templates/email/',
            false
        );

        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($to, $subject, $message, $headers);
    }

    /**
     * Check retail user access. 
     * Whether allow them to log in or not.
     *
     * @param WP_User $user
     * @return WP_User
     */
    public function authenticate_retail_user($user)
    {

        if ($user instanceof \WP_User) {
            if (in_array(UserRoles::ROLE_DSI_RETAIL, (array) $user->roles)) {

                if (empty(get_user_meta($user->ID, UserMeta::META_TECH_PUB_ACCESS_ALLOWED, true))) {
                    $user = new \WP_Error('authentication_failed', __('<strong>ERROR</strong>: Your registration is pending approval, and will be complete once your information has been verified.'));
                }
            }
        }

        return $user;
    }
}
