<?php

namespace DSI\TechPub;


defined('ABSPATH') || exit;

/**
 * Class PageTemplater
 * @package DSI\TechPub
 */

class PageTemplater
{
    /**
     * Template files list to add.
     *
     * @var $templates
     */
    public $templates = array(
        'dsi-tech-pub-library.php' => 'DSI Tech Pub Library',
    );

    /**
     * Construct the plugin.
     **/
    public function __construct()
    {
        add_filter('theme_page_templates', array($this, 'add_page_template'));
        add_filter('page_template', array($this, 'redirect_page_template'));
    }

    /**
     * Add page template.
     * 
     * @param array $templates
     * @return array
     **/
    public function add_page_template($templates)
    {
        if (!empty($this->templates)) {
            foreach ($this->templates as $template_file => $template_name) {
                $templates[$template_file] = $template_name;
            }
        }

        return $templates;
    }

    /**
     * load page template.
     * 
     * @param string $template
     * @return string
     **/
    public function redirect_page_template($template)
    {
        $post = get_post();
        $page_template = get_post_meta($post->ID, '_wp_page_template', true);

        if (!empty($this->templates)) {
            foreach ($this->templates as $template_file => $template_name) {
                if ($template_file == basename($page_template)) {
                    $template = DSI_CUST_PLUGIN_DIR_PATH . '/templates/woocommerce/' . $template_file;
                }
            }
        }

        return $template;
    }
}
