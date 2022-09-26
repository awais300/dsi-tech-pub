<?php

namespace DSI\TechPub;

use DSI\TechPub\TemplateLoader;
use DSI\TechPub\Filters\Filters;
use DSI\TechPub\User\UserMeta;
use DSI\TechPub\User\UserRoles;


defined('ABSPATH') || exit;

/**
 * Class TechPubLib
 * @package DSI\TechPub
 */

class TechPubLib extends Singleton
{
    /**
     * The template loader.
     *
     * @var $loader
     */
    public $loader = null;

    /**
     * The download key to be used in URL as query string.
     *
     * @var DOWNLOAD_KEY
     */
    public const DOWNLOAD_KEY = 'tech_pub_file_id';

    /**
     * Construct the plugin.
     */
    public function __construct()
    {
        //$this->download_media('');
        $this->loader = TemplateLoader::get_instance();

        add_action('wp', array($this, 'remove_add_to_cart_button'));
        add_filter('woocommerce_add_to_cart_redirect', array($this, 'go_to_checkout_page'));

        add_action('init', array($this, 'download_media'));

        // Delete transient.
        add_action('save_post', array($this, 'delete_dsi_transient'));
        add_action('delete_post', array($this, 'delete_dsi_transient'));
    }

    /**
     *Remove add to cart button on normal WooCommerce page.
     *
     **/
    public function remove_add_to_cart_button()
    {
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 30);
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    }

    /**
     * Get tech pub products with filter features as well.
     * 
     * @return WC_Product
     */
    public function get_tech_pub_products()
    {
        $products = (Filters::get_instance())->get_tech_pub_products();
        return $products;
    }

    /**
     * Download file.
     **/
    public function download_media()
    {
        if (!is_user_logged_in()) {
            $this->unauthorized();
        }

        if (
            isset($_GET[self::DOWNLOAD_KEY]) &&
            !empty($_GET[self::DOWNLOAD_KEY])
        ) {

            $media_id = $_GET[self::DOWNLOAD_KEY];
            $url = wp_get_attachment_url($media_id);

            if (empty($url)) {
                $this->unauthorized();
            }

            (Helper::get_instance())->force_download($url);
        }
    }

    /**
     * Check if current user has access permission to download the media.
     * 
     * @param  int  $product_id
     * @return boolean
     */
    public function is_user_has_access($product_id)
    {
        if (current_user_can('manage_options') || (UserRoles::get_instance())->is_staff_user()) {
            return true;
        } else if ((UserRoles::get_instance())->is_distributor_plus_user() || (UserRoles::get_instance())->is_distributor_user()) {
            $future_date = (UserMeta::get_instance())->get_user_meta(get_current_user_id(), UserMeta::META_TECH_PUB_ACCESS_EXPIRATION_DATE);
            if ($this->is_distributor_type_users_access_expired($future_date) === false) {
                return true;
            } else {
                return false;
            }
        } else {
            // Retail user only can access media if they have ordered/purchased.
            if ((Order::get_instance())->has_user_access_to_media($product_id)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Invalid acccess. Set HTTP status to 403.
     * 
     **/
    public function unauthorized()
    {
        status_header(403, 'Forbidden');
        exit;
    }

    /**
     * Chek if distributor and distributor plus users access expired.
     *
     * @param $future_data
     * @return boolean
     **/
    public function is_distributor_type_users_access_expired($future_date)
    {
        $future_date = strtotime($future_date);
        $now = time();

        if ($now > $future_date) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Take the user to checkout page right after product is added to cart.
     * 
     * @param string $url
     * @return string
     **/
    public function go_to_checkout_page($url)
    {
        return wc_get_checkout_url();
    }

    /**
     * Delete transient.
     **/
    public function delete_dsi_transient()
    {
        delete_transient(Filters::TRANSIENT_MAKE);
        delete_transient(Filters::TRANSIENT_MODEL);
    }
}
