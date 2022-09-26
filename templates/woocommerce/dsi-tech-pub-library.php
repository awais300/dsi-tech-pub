<?php
namespace DSI\TechPub;

/**
 * Template Name: DSI Tech Pub Library
 *
 * This file is loaded via hooks and set as Page Template via hooks as well.
 * The file is residing in /plugin/templates/woocommerce/dsi-tech-pub-library.php
 * Though its a regular page template but its being put inside the "woocommerce" directory as 
 * its will be displaying woocommerce products.
 * 
 **/
get_header();
?>

<?php
$products = (TechPubLib::get_instance())->get_tech_pub_products();
?>

<div id="primary" <?php astra_primary_class(); ?>>

        <?php astra_primary_content_top(); ?>

        <?php astra_content_page_loop(); ?>

        <?php
                $button_url = get_site_url() . '?quantity=1&add-to-cart=';
                $download_url = get_site_url() . '?tech_pub_file_id=';
                foreach($products as $product) {
                        echo "<br/>";
                        echo $product->get_name();
                        echo "<br/>";

                        $add_url = $button_url . $product->get_id();
                        $id = $product->get_meta('upload_file', true);
                        $down_url = $download_url . $id;
                        
                        echo "<a href='{$add_url}'>Add to Cart</a>";
                        echo "<br/>";
                        echo "<a href='{$down_url}'>Download File</a>";

                }
        ?>

        <?php astra_primary_content_bottom(); ?>

    </div><!-- #primary -->


<?php get_footer(); ?>