<?php

namespace DSI\TechPub;

use DSI\TechPub\User\UserLogin;
use DSI\TechPub\User\UserMeta;
use DSI\TechPub\ACF\ACF;
use DSI\TechPub\Filters\Filters;

defined('ABSPATH') || exit;

/**
 * Class Bootstrap
 * @package DSI\TechPub
 */

class Bootstrap
{

	private $version = '1.0.0';

	/**
	 * Instance to call certain functions globally within the plugin.
	 *
	 * @var _instance
	 */
	protected static $_instance = null;

	/**
	 * Construct the plugin.
	 */
	public function __construct()
	{
		add_action('init', array($this, 'load_plugin'), 0);
	}

	/**
	 * Main Bootstrap instance.
	 *
	 * Ensures only one instance is loaded or can be loaded.
	 *
	 * @static
	 * @return self Main instance.
	 */
	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Determine which plugin to load.
	 */
	public function load_plugin()
	{
		$this->define_constants();
		$this->init_hooks();
	}

	/**
	 * Define WC Constants.
	 */
	private function define_constants()
	{
		// Path related defines
		$this->define('DSI_CUST_PLUGIN_FILE', DSI_CUST_PLUGIN_FILE);
		$this->define('DSI_CUST_PLUGIN_BASENAME', plugin_basename(DSI_CUST_PLUGIN_FILE));
		$this->define('DSI_CUST_PLUGIN_DIR_PATH', untrailingslashit(plugin_dir_path(DSI_CUST_PLUGIN_FILE)));
		$this->define('DSI_CUST_PLUGIN_DIR_URL', untrailingslashit(plugins_url('/', DSI_CUST_PLUGIN_FILE)));
	}

	/**
	 * Collection of hooks.
	 */
	public function init_hooks()
	{
		add_action('init', array($this, 'load_textdomain'));
		add_action('init', array($this, 'init'), 1);

		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
	}

	/**
	 * Localization.
	 */
	public function load_textdomain()
	{
		load_plugin_textdomain('dsi-customization', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	}

	/**
	 * Initialize the plugin.
	 */
	public function init()
	{
		new ACF();
		new Filters();
		new UserLogin();
		new UserMeta();
		new Order();
		new TechPubLib();
	}

	/**
	 * Enqueue all styles.
	 */
	public function enqueue_styles()
	{
		global $post;
		if ($post->post_name == TechPubLib::TECH_PUB_PAGE) {
			wp_enqueue_style('dsi-w3', DSI_CUST_PLUGIN_DIR_URL . '/assets/css/w3.css', array(), null, 'all');
			wp_enqueue_style('yips-customization-frontend', DSI_CUST_PLUGIN_DIR_URL . '/assets/css/dsi-customization-frontend.css', array(), null, 'all');
		}
	}


	/**
	 * Enqueue all scripts.
	 */
	public function enqueue_scripts()
	{
		global $post;
		if ($post->post_name == TechPubLib::TECH_PUB_PAGE) {
			wp_enqueue_script('dsi-customization-frontend', DSI_CUST_PLUGIN_DIR_URL . '/assets/js/dsi-customization-frontend.js', array('jquery'));
		}
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param  string $name
	 * @param  string|bool $value
	 */
	public function define($name, $value)
	{
		if (!defined($name)) {
			define($name, $value);
		}
	}
}
