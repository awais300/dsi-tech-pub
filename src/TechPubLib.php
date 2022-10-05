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
     * The product search param.
     *
     * @array $search_param
     */
    public $search_param = array();

    /**
     * Tech pub page slug.
     *
     * @var TECH_PUB_PAGE
     */
    public const TECH_PUB_PAGE = 'tech-pub-library';


    /**
     * The download key to be used in URL as query string.
     *
     * @var DOWNLOAD_KEY
     */
    public const DOWNLOAD_KEY = 'tech_pub_file_id';

    /**
     * The product ID key.
     *
     * @var PRODUCT_ID
     */
    public const PRODUCT_ID = 'tech_pub_product_id';

    /**
     * Construct the plugin.
     */
    public function __construct()
    {
        $this->loader = TemplateLoader::get_instance();

        add_action('wp', array($this, 'remove_add_to_cart_button'));
        add_filter('woocommerce_add_to_cart_redirect', array($this, 'go_to_checkout_page'));

        add_action('init', array($this, 'add_shortcode'));
        add_action('init', array($this, 'download_media'));

        // Delete transient.
        add_action('save_post', array($this, 'delete_dsi_transient'));
        add_action('delete_post', array($this, 'delete_dsi_transient'));
    }

    /**
     * Register the shortcode.
     */
    public function add_shortcode()
    {
        add_shortcode('tech_pub_lib', array($this, 'display_tech_pub_lib_content'));
    }

    /**
     * Display the tech pub lib page content.
     */
    public function display_tech_pub_lib_content()
    {
        $product_query_obj = $this->get_tech_pub_products($this->get_product_search_param());

        $data = array(
            'category_options' => (Filters::get_instance())->get_categories(),
            'make_options' => (Filters::get_instance())->get_aircraft_make(),
            'model_options' => (Filters::get_instance())->get_aircraft_model(),
            'product_query_obj' => $product_query_obj,
            'pagination' => $this->get_pagination($product_query_obj),
        );

        $content = $this->loader->get_template(
            'dsi-tech-pub-library.php',
            $data,
            DSI_CUST_PLUGIN_DIR_PATH . '/templates/woocommerce/',
            false
        );

        return $content;
    }


    /**
     * Get pagination HTML.
     * 
     * @param WC_Porduct $post_obj
     * @return string
     **/
    public function get_pagination($post_obj)
    {
        $total_pages = $post_obj->max_num_pages;
        if ($total_pages > 1) {
            $big = 99999999999;
            return paginate_links(array(
                'base'      => str_replace(array($big, '#038;'), array('%#%', ''), esc_url(get_pagenum_link($big))),
                'format'    => '?paged=%#%',
                'prev_text'    => __('«'),
                'next_text'    => __('»'),
                'current'   => max(1, get_query_var('paged')),
                'type'      => 'plain',
                'total'     => $total_pages,
            ));
        }
    }

    /**
     * Get product search parameters.
     * 
     * @return array
     **/
    public function get_product_search_param()
    {

        $search_param = array();
        if (isset($_GET['dsi-search']) && $_GET['dsi-search'] === 'dsi-search') {
            if (!wp_verify_nonce($_GET['_wpnonce'], 'search-form-nonce')) {
                //wp_die('Invalid Access.');
                wp_redirect('/');
                exit;
            }

            $search_param['s_keyword'] = sanitize_text_field($_GET['keyword']);
            $search_param['s_category'] = sanitize_text_field($_GET['category']);
            $search_param['s_aircraft_make'] = sanitize_text_field($_GET['aircraft_make']);
            $search_param['s_aircraft_model'] = sanitize_text_field($_GET['aircraft_model']);
        }

        return $search_param;
    }

    /**
     * Get tech pub products with filter features as well.
     * 
     * @param array $search_param
     * @return WC_Product
     */
    public function get_tech_pub_products($search_param = array())
    {
        $products = (Filters::get_instance())->get_products($search_param);
        return $products;
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
     * Download file if user has permission.
     * 
     **/
    public function download_media()
    {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }

        if (
            isset($_GET[self::DOWNLOAD_KEY]) &&
            !empty($_GET[self::DOWNLOAD_KEY]) &&

            isset($_GET[self::PRODUCT_ID]) &&
            !empty($_GET[self::PRODUCT_ID])
        ) {
            // Nonce verification.
            if (!wp_verify_nonce($_GET['_wpnonce'], 'media-url-nonce')) {
                $this->unauthorized();
            }

            // User auth verification.
            if (!is_user_logged_in()) {
                $this->unauthorized();
            }

            $media_id = $_GET[self::DOWNLOAD_KEY];
            $url = wp_get_attachment_url($media_id);

            if (empty($url)) {
                $this->unauthorized();
            }

            $product_id = $_GET[self::PRODUCT_ID];
            if ($this->is_user_has_access($product_id)) {
                (Helper::get_instance())->force_download($url);
            } else {
                $this->unauthorized();
            }
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
            $access = (UserMeta::get_instance())->get_user_meta(get_current_user_id(), UserMeta::META_TECH_PUB_ACCESS_ALLOWED);
            // /if ($this->is_distributor_type_users_access_expired($future_date) === false && !empty($access)) {
            if (!empty($access)) {
                return true; // Not expired.
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
     * Invalid access. Set HTTP status to 403.
     * 
     **/
    public function unauthorized()
    {
        status_header(403, 'Forbidden');
        exit;
    }

    /**
     * Check if distributor and distributor plus users access expired.
     *
     * @param string $future_data
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
     * Get the product URL.
     *
     * @param int $product_id
     * @return string
     **/
    public function get_product_add_url($product_id)
    {
        if (empty($product_id)) {
            return 'javascript:void(0);';
        }

        $data = array(
            'add-to-cart' => $product_id,
            'quantity' => 1,
        );

        $query_str = http_build_query($data);
        $product_add_url = get_site_url() . '/?' . $query_str;
        return $product_add_url;
    }

    /**
     * Get the media URL.
     *
     * @param int $media_id
     * @return string
     **/
    public function get_media_url($media_id, $product_id)
    {
        if (empty($media_id)) {
            return 'javascript:void(0);';
        }

        $data = array(
            self::DOWNLOAD_KEY => $media_id,
            self::PRODUCT_ID => $product_id,
            '_wpnonce' => wp_create_nonce('media-url-nonce'),
        );

        $query_str = http_build_query($data);
        $media_url = get_site_url() . '/?' . $query_str;
        return $media_url;
    }

    /**
     * Take the user to checkout page right after product is added to cart.
     * 
     * @param string $url
     * @return string
     **/
    public function go_to_checkout_page($url)
    {
        return wc_get_cart_url();
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
