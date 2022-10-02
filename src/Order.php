<?php

namespace DSI\TechPub;

use DSI\TechPub\ACF\ACF;
use DSI\TechPub\User\UserRoles;

defined('ABSPATH') || exit;

/**
 * Class Order
 * @package DSI\TechPub
 */

class Order extends Singleton
{
    /**
     * Media ID key to store against order item.
     *
     * @var META_ORDER_ITEM_MEDIA_ID
     */
    public const META_ORDER_ITEM_MEDIA_ID = '_order_item_media_id';

    /**
     * Holds UNIX timestamp when access granted (product is purchased). 
     *
     * @var META_ORDER_ITEM_MEDIA_ACCESS_TIME
     */
    public const META_ORDER_ITEM_MEDIA_ACCESS_TIME = '_order_item_media_access_time';

    /**
     * Orders cache key.
     *
     * @var CACHE_ORDERS
     */
    public const CACHE_ORDERS = 'dsi_cache_orders';

    /**
     * Number of hours to allow for file access. 
     *
     * @var HOURS_ALLOWED_TO_ACCESS_FILE
     */
    public const HOURS_ALLOWED_TO_ACCESS_FILE = 48;

    /**
     * Allowed order statuses list.
     *
     * @array $allowed_order_statuses
     */
    public $allowed_order_statuses = array(
        'wc-processing',
        'wc-on-hold',
        'wc-completed',
        'wc-pending',
    );

    /**
     * Construct the plugin.
     */
    public function __construct()
    {
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'checkout_create_order_line_item'), 20, 4);
        add_action('woocommerce_order_item_meta_start', array($this, 'order_item_meta_start'), 20, 4);
    }

    /**
     * Save media ID against each order item.
     * 
     **/
    public function checkout_create_order_line_item($item, $cart_item_key, $values, $order)
    {
        $media_id = get_post_meta($item->get_product_id(), ACF::UPLOAD_FIELD_KEY, true);
        if (!empty($media_id)) {
            $item->update_meta_data(self::META_ORDER_ITEM_MEDIA_ID, $media_id);
            $item->update_meta_data(self::META_ORDER_ITEM_MEDIA_ACCESS_TIME, time());
        } else {
            wc_add_notice('No media is attached to one of the product in the cart. Please contact site administrator.', 'error');
        }
    }

    /**
     * Check if current user has access permission to download the media.
     * 
     * @param  int  $product_id
     * @return boolean
     */
    public function has_user_access_to_media($product_id)
    {
        if(empty($product_id)){
            return false;
        }

        // Retail user only can access media if they have ordered/purchased.
        if ((UserRoles::get_instance())->is_retail_user() === false) {
            return false;
        }

        $args = array(
            'customer_id' => get_current_user_id(),
            'status' => $this->allowed_order_statuses,
            'limit' => -1,
        );

        // Per user cache key.
        $cache_key = self::CACHE_ORDERS . '_' . get_current_user_id();

        // Optimize query.
        $orders = wp_cache_get($cache_key);
        if (false === $orders) {
            $orders = wc_get_orders($args);
            wp_cache_set($cache_key, $orders, '', 10 * MINUTE_IN_SECONDS);
        }

        if (empty($orders)) {
            return false;
        }

        foreach ($orders as $order) {
            foreach ($order->get_items() as $item_id => $item) {
                $order_item_id = $item->get_product_id();
                $media_id_in_post = get_post_meta($order_item_id, ACF::UPLOAD_FIELD_KEY, true);

                if ($order_item_id == $product_id && !empty($media_id_in_post)) {

                    $unix_time = $item->get_meta(self::META_ORDER_ITEM_MEDIA_ACCESS_TIME, true);
                    $media_id = $item->get_meta(self::META_ORDER_ITEM_MEDIA_ID, true);

                    if ($media_id == $media_id_in_post && $this->is_access_expired($unix_time)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Display something on on front facing user dashboard under order page.
     * 
     * @param  int $item_id
     * @param  WC_Order_Item $item
     * @param  WC_Order $order
     * @param  boolean $bool
     */
    public function order_item_meta_start($item_id, $item, $order, $bool)
    {
        if (is_admin()) {
            return;
        }

        // Retail user only can access media if they have ordered/purchased.
        if ((UserRoles::get_instance())->is_retail_user() === false) {
            return;
        }

        if (is_wc_endpoint_url('view-order')) {
            $order_item_id = $item->get_product_id();
            $media_id_in_post = get_post_meta($order_item_id, ACF::UPLOAD_FIELD_KEY, true);

            if (!empty($media_id_in_post)) {
                $unix_time = $item->get_meta(self::META_ORDER_ITEM_MEDIA_ACCESS_TIME, true);
                $media_id = $item->get_meta(self::META_ORDER_ITEM_MEDIA_ID, true);

                if ($media_id == $media_id_in_post && $this->is_access_expired($unix_time)) {
                    $media_url = (TechPubLib::get_instance())->get_media_url($media_id);
                    echo " : <a href='{$media_url}'>Download tech publication File</a>";
                }
            }
        }
    }

    /**
     * Check if hours are expired.
     * 
     * @param  int  $date
     * @return boolean
     */
    public function is_access_expired($date)
    {
        $access_hours = $this->get_time_diff_in_hours($date);
        if ($access_hours <= self::HOURS_ALLOWED_TO_ACCESS_FILE) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get time difference in hours. 
     * Takes a past $date in strtotime.
     * 
     * @param  int $date
     * @return int
     */
    public function get_time_diff_in_hours($date)
    {
        $now = time();
        $seconds = $now - $date;
        $hours = $seconds / 60 / 60;
        return $hours;
    }
}
