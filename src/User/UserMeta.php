<?php

namespace DSI\TechPub\User;

use DSI\TechPub\Singleton;
use DSI\TechPub\TemplateLoader;

defined('ABSPATH') || exit;

/**
 * Class UserMeta
 * @package DSI\TechPub
 */

class UserMeta extends Singleton
{
    /**
     * The template loader.
     *
     * @var $loader
     */
    public $loader = null;

    /**
     * Customer access info.
     *
     * @var META_TECH_PUB_ACCESS_ALLOWED
     */
    public const META_TECH_PUB_ACCESS_ALLOWED = 'dsi_tech_pub_access_allowed';

    /**
     * Access expiration date.
     *
     * @var META_TECH_PUB_ACCESS_EXPIRATION_DATE
     */
    public const META_TECH_PUB_ACCESS_EXPIRATION_DATE = 'dsi_tech_pub_access_expiration_date';

    /**
     * Construct the plugin.
     **/
    public function __construct()
    {
        $this->loader = TemplateLoader::get_instance();
        add_action('acp/editing/saved', array($this, 'on_tech_pub_access'), 10, 3);
    }

    /**
     * Save customer information.
     *
     * @param int $user_id
     * @param Array $user_data
     */
    public function save_user_meta($user_id, $meta_key, $user_data)
    {
        if (empty($user_id)) {
            throw new \Exception('User ID is missing');
        }

        update_user_meta($user_id, $meta_key, $user_data);
    }

    /**
     * Get customer information.
     *
     * @param int $user_id
     * @return Array
     */
    public function get_user_meta($user_id, $meta_key)
    {
        if (empty($user_id)) {
            return false;
        }

        return get_user_meta($user_id, $meta_key, true);
    }

    /**
     * Check access and trigger notification to customer if access is allowed.
     *
     * @param \Ac\Column $column
     * @param int $id
     * @param  mixed $value
     **/
    public function on_tech_pub_access(\AC\Column $column, $id, $value)
    {
        if ($column instanceof \AC\Column\CustomField && self::META_TECH_PUB_ACCESS_ALLOWED === $column->get_meta_key() && 'user' === $column->get_meta_type()) {

            $val = get_user_meta($id, self::META_TECH_PUB_ACCESS_ALLOWED, true);
            if ($val == 1) {
                $this->customer_allowed_access_notification($id);
            }
        }

        if ($column instanceof \AC\Column\CustomField && self::META_TECH_PUB_ACCESS_EXPIRATION_DATE === $column->get_meta_key() && 'user' === $column->get_meta_type()) {

            // Disable access if expiration date is in the past (expired).
            //$this->update_tech_access_by_expiration_date($id);
        }
    }

    /**
     * Send customer email that access is granted.
     * 
     * @param  int $user_id
     */
    public function customer_allowed_access_notification($user_id)
    {
        $user = get_userdata($user_id);

        $data = array(
            'user' => $user,
        );

        $to = $user->user_email;
        $subject = 'Tech Library Access Granted';
        $message = $this->loader->get_template(
            'user-allowed-access-notification.php',
            $data,
            DSI_CUST_PLUGIN_DIR_PATH . '/templates/email/',
            false
        );

        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($to, $subject, $message, $headers);
    }

    /**
     * Set access based on expiration date.
     * 
     * @param  int $user_id
     */
    public function update_tech_access_by_expiration_date($user_id)
    {
        $access_allowed = get_user_meta($user_id, self::META_TECH_PUB_ACCESS_ALLOWED, true);
        $expiration_date = get_user_meta($user_id, self::META_TECH_PUB_ACCESS_EXPIRATION_DATE, true);

        $now = new \DateTime();
        $saved_date = new \DateTime($expiration_date);

        if ($saved_date < $now && $access_allowed != 0) {
            update_user_meta($user_id, self::META_TECH_PUB_ACCESS_ALLOWED, 0);
        }
    }
}
