<?php

namespace DSI\TechPub;


defined('ABSPATH') || exit;

/**
 * Class Cart
 * @package DSI\TechPub
 */

class Cart
{
    /**
     * Construct the plugin.
     */
    public function __construct()
    {
        add_action('woocommerce_cart_collaterals', array($this, 'add_continue_shopping_button'));
        add_action('template_redirect', array($this, 'send_user_to_tech_pub_page'));
        add_action('wp_head', array($this, 'hide_view_cart_button'));
    }

    public function hide_view_cart_button()
    {

        if (is_cart()) {
?>
            <style>
                .woocommerce-message a.wc-forward {
                    display: none !important
                }
            </style>
<?php
        }
    }

    public function add_continue_shopping_button()
    {
        $tech_pub_page = TechPubLib::TECH_PUB_PAGE;
        echo "<div class='browse-more'>
                <a class='button' href='/{$tech_pub_page}'>Return to Tech Pubs</a>
            </div>";
    }

    public function send_user_to_tech_pub_page()
    {
        if (is_shop() || is_product_category() || is_product_tag() || is_product()) {
            wp_redirect('/' . TechPubLib::TECH_PUB_PAGE);
            exit();
        }
    }
}
