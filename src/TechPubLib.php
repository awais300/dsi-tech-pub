<?php

namespace DSI\TechPub;

use DSI\TechPub\TemplateLoader;
use DSI\TechPub\Filters\Filters;

defined('ABSPATH') || exit;

/**
 * Class TechPubLib
 * @package DSI\TechPub
 */

class TechPubLib
{
    /**
     * The template loader.
     *
     * @var $loader
     */
    public $loader = null;

    /**
     * Construct the plugin.
     */
    public function __construct()
    {
        $this->loader = TemplateLoader::get_instance();
        add_action('save_post', array($this, 'delete_dsi_transient'));
        add_action('delete_post', array($this, 'delete_dsi_transient'));
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
