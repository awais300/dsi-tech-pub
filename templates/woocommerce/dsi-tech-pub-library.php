<?php
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

<div id="primary" <?php astra_primary_class(); ?>>

        <?php astra_primary_content_top(); ?>

        <?php astra_content_page_loop(); ?>

        The Tech Pub Libraray is coming soon!

        <?php astra_primary_content_bottom(); ?>

    </div><!-- #primary -->


<?php get_footer(); ?>