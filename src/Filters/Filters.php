<?php

namespace DSI\TechPub\Filters;

use ACP\Helper\Select\Group\UserRole;
use DSI\TechPub\Singleton;
use DSI\TechPub\TemplateLoader;
use DSI\TechPub\User\UserRoles;

defined('ABSPATH') || exit;

/**
 * Class Filters
 * @package DSI\TechPub
 */

class Filters extends Singleton
{
    /**
     * The post type product.
     *
     * @var POST_TYPE
     */
    public const POST_TYPE = 'product';

    /**
     * The product taxonomy.
     *
     * @var POST_TYPE
     */
    public const CATEGORY = 'product_cat';

    /**
     * Make transient key.
     *
     * @var TRANSIENT_MAKE
     */
    public const TRANSIENT_MAKE = 'dsi_transient_make';

    /**
     * Model transient key.
     *
     * @var TRANSIENT_MAKE
     */
    public const TRANSIENT_MODEL = 'dsi_transient_model';

    /**
     * Product per page.
     *
     * @var PRODUCTS_PER_PAGE
     */
    public const PRODUCTS_PER_PAGE = 1;

    /**
     * Distributor plus categories. 
     * Contains list of category slugs.
     *
     * @array $distributor_plus_categories
     */
    public $distributor_plus_categories = array(
        'music',
    );

    /**
     * Construct the plugin.
     **/
    public function __construct()
    {
        add_filter('posts_where', array($this, 'add_like_clause_for_title_and_description'), 10, 2);
    }

    /**
     * Get aircraft make values.
     * 
     * @return array
     */
    public function get_aircraft_make()
    {
        $result = get_transient(self::TRANSIENT_MAKE);
        if (false === $result) {
            $args = array(
                'post_type'        => self::POST_TYPE,
                'posts_per_page'   => -1,
                'post_status' => array('publish'),
                'meta_query'       => array(
                    'relation'    => 'AND',
                    array(
                        'key'          => 'aircraft_make',
                        'value'        => '',
                        'compare'      => '!=',
                    )
                ),

            );

            $result = get_posts($args);
            set_transient(self::TRANSIENT_MAKE, $result, WEEK_IN_SECONDS);
        }

        $data = array();
        foreach ($result as $post) {
            $data[$post->aircraft_make] = $post->aircraft_make;
        }

        $data = array_unique($data);
        return $data;
    }

    /**
     * Get aircraft model values.
     * 
     * @return array
     */
    public function get_aircraft_model()
    {
        $result = get_transient(self::TRANSIENT_MODEL);
        if (false === $result) {
            $args = array(
                'post_type'        => self::POST_TYPE,
                'posts_per_page'   => -1,
                'post_status' => array('publish'),
                'meta_query'       => array(
                    'relation'    => 'AND',
                    array(
                        'key'          => 'aircraft_model',
                        'value'        => '',
                        'compare'      => '!=',
                    )
                ),

            );

            $result = get_posts($args);
            set_transient(self::TRANSIENT_MODEL, $result, WEEK_IN_SECONDS);
        }

        $data = array();
        foreach ($result as $post) {
            $data[$post->aircraft_model] = $post->aircraft_model;
        }

        $data = array_unique($data);
        return $data;
    }

    /**
     * Get categories based on the user role.
     * 
     * @return array
     **/
    public function get_categories()
    {
        $terms = get_terms(array(
            'taxonomy' => self::CATEGORY,
            'parent'   => 0,
            'hide_empty' => 0,
        ));

        if (is_wp_error($terms)) {
            $error = $terms->get_error_message();
            throw new \Exception('Error: ' . $error);
        }

        $categories = array();
        if (!empty($terms)) {
            foreach ($terms as $term) {
                $categories[$term->slug] = $term->name;
            }
        }

        // Remove uncategorized term explicitly.
        unset($categories['uncategorized']);

        // Admin or Staff employee have access to all categories.
        if (current_user_can('manage_options') || (UserRoles::get_instance())->is_staff_user()) {
            return $categories;
        }

        // If user is not distributor plus exclude distributor plus categories.
        if (!(UserRoles::get_instance())->is_distributor_plus_user()) {
            foreach ($this->distributor_plus_categories as $dist_cat) {
                unset($categories[$dist_cat]);
            }
        }

        return $categories;
    }

    /**
     * Get tech pub products optionally by search too.
     * 
     * @param array $search_param
     * @return WC_Product
     **/
    function get_products($search_param = array())
    {
        $search_param = array_filter($search_param);
        $paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;

        if (empty($search_param)) {
            $args = array(
                'status'            => array('publish'),
                'category'          => array_keys($this->get_categories()),
                'limit'             => self::PRODUCTS_PER_PAGE,
                'page'              => $paged,
                'orderby'           => 'date',
                'order'             => 'DESC',
                'paginate'          => true,
            );
            $result = wc_get_products($args);
            return $result;
        } else {

            $s_keyword = '';
            $s_aircraft_make = '';
            $s_aircraft_model = '';
            $s_category = '';

            // If keyword/text is set.
            if (isset($search_param['s_keyword']) && !empty($search_param['s_keyword'])) {
                $search_val = $search_param['s_keyword'];
                $s_keyword =  array(
                    'relation'    => 'OR',
                    array(
                        'key'          => 'part_numbers',
                        'value'        => $search_val,
                        'compare'      => 'LIKE',
                    ),
                    array(
                        'key'          => 'user_guide_number',
                        'value'        => $search_val,
                        'compare'      => 'LIKE',
                    ),
                    array(
                        'key'          => 'revision_number',
                        'value'        => $search_val,
                        'compare'      => 'LIKE',
                    ),
                    array(
                        'key'          => 'supported_products',
                        'value'        => $search_val,
                        'compare'      => 'LIKE',
                    ),
                );
            }

            // If aircraft make is set.
            if (isset($search_param['s_aircraft_make']) && !empty($search_param['s_aircraft_make'])) {
                $search_val = $search_param['s_aircraft_make'];
                $s_aircraft_make = array(
                    'relation'    => 'AND',
                    array(
                        'key'          => 'aircraft_make',
                        'value'        => $search_val,
                        'compare'      => '=',
                    ),
                );
            }

            // If aircraft model is set.
            if (isset($search_param['s_aircraft_model']) && !empty($search_param['s_aircraft_model'])) {
                $search_val = $search_param['s_aircraft_model'];
                $s_aircraft_model = array(
                    'relation'    => 'AND',
                    array(
                        'key'          => 'aircraft_model',
                        'value'        => $search_val,
                        'compare'      => '=',
                    ),
                );
            }

            // If category is set.
            if (isset($search_param['s_category']) && !empty($search_param['s_category'])) {
                $search_val = $search_param['s_category'];
                $s_category = array(
                    'relation'    => 'AND',
                    array(
                        'taxonomy'  => self::CATEGORY,
                        'terms'     => array($search_val),
                        'field'     => 'slug',
                    )
                );
            }

            // Get IDs based on the search parameters.
            $args = array(
                'post_type'        => self::POST_TYPE,
                'post_status' => array('publish'),
                'posts_per_page'   => -1,
                'fields'   => 'ids',
                'suppress_filters' => false,
                'title_and_desc' => $search_param['s_keyword'],
                'meta_query'       => array(
                    'relation'    => 'AND',
                    $s_keyword,
                    $s_aircraft_make,
                    $s_aircraft_model,
                ),
                'tax_query' => array(
                    'relation' => 'AND',
                    $s_category
                )

            );
            $ids_array = get_posts($args);

            // Get products based on the found IDs.
            $product_category = array();
            if (isset($search_param['s_category']) && !empty($search_param['s_category'])) {
                if ($this->is_category_allowed_to_current_user($search_param['s_category'])) {
                    $product_category = $search_param['s_category'];
                }
            }

            $args = array(
                'status'            => array('publish'),
                'include'           => $ids_array,
                'category'          => $product_category,
                'limit'             => self::PRODUCTS_PER_PAGE,
                'page'              => $paged,
                'orderby'           => 'date',
                'order'             => 'DESC',
                'paginate'          => true,
            );

            $result = wc_get_products($args);
            return $result;
            $products = $result->products;
            return $products;
        }
    }

    /**
     * Add SQL LIKE clause to search for post_title and post_content in WP_Query.
     *
     * @param string $where
     * @param WP_Query $wp_query
     * @return string
     **/
    public function add_like_clause_for_title_and_description($where, $wp_query)
    {
        global $wpdb;
        if ($search_term = $wp_query->get('title_and_desc')) {
            $where .= ' OR (' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql($search_term) . '%\'';
            $where .= ' OR ' . $wpdb->posts . '.post_content LIKE \'%' . esc_sql($search_term) . '%\')';
        }

        return $where;
    }

    /**
     * Check if category belongs allowed to current logged in user.
     * 
     * @param  string $category
     * @return boolean
     */
    public function is_category_allowed_to_current_user($category)
    {
        if (
            current_user_can('manage_options') ||
            (UserRoles::get_instance())->is_distributor_plus_user() ||
            (UserRoles::get_instance())->is_staff_user()
        ) {
            return true;
        } else {
            if (in_array($category, $this->distributor_plus_categories)) {
                return false;
            } else {
                return true;
            }
        }
    }
}
