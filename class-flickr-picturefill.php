<?php
/**
 * @package   Flickr Picturefill
 * @author    Adam Wills <adam@adamwills.com>
 * @license   MIT/GPLv2
 * @link      http://adamwills.com
 * @copyright 2013 Adam Wills
 */

/**
 * Plugin class.
 *
 * @package Flickr_Picturefill
 * @author  Your Name <email@example.com>
 */
class Flickr_Picturefill {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @const   string
	 */
	const VERSION = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'flickr-picturefill';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . 'flickr-picturefill.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		// Load admin style sheet and JavaScript.
		// add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		// add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );


		add_shortcode( 'picturefill', array( $this, 'create_shortcode' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		// TODO: Define activation functionality here
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		// TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/lang/' );
	}



	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function register_scripts() {
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * TODO:
		 *
		 * Change 'Page Title' to the title of your plugin admin page
		 * Change 'Menu Text' to the text for menu item for the plugin settings page
		 * Change 'plugin-name' to the name of your plugin
		 */
		$this->plugin_screen_hook_suffix = add_plugins_page(
			__( 'Flickr Picturefill', $this->plugin_slug ),
			__( 'Flickr Picturefill Settings', $this->plugin_slug ),
			'read',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'plugins.php?page=flickr-picturefill' ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function create_shortcode( $atts ) {
		
		extract( shortcode_atts( array(
			'id' => '0',
			'alt' => '',
		), $atts ) );

		$params = array(
			'api_key'	=> 'a7243d919dd70c951623cc75b5b1b3f8',
			'method'	=> 'flickr.photos.getSizes',
			'photo_id'	=> $atts['id'],
			'format'	=> 'php_serial',
		);

		$encoded_params = array();

		foreach ($params as $k => $v){
			$encoded_params[] = urlencode($k).'='.urlencode($v);
		}

		$url = "http://api.flickr.com/services/rest/?".implode('&', $encoded_params);

		$rsp = file_get_contents($url);

		$rsp_obj = unserialize($rsp);

		if ($rsp_obj['stat'] == 'ok'){


			wp_register_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/picturefill.min.js', __FILE__ ) );
			wp_enqueue_script( $this->plugin_slug . '-plugin-script' );

			$sizes = $rsp_obj['sizes']['size'];
			$output = '<span data-picture data-alt="'.$atts['alt'].'">';
			$output.= '<span data-src="'.$sizes[4]['source'].'"></span>';
			$output.= '<span data-src="'.$sizes[7]['source'].'" data-media="(min-width: 400px)"></span>';
    		$output.= '<span data-src="'.$sizes[8]['source'].'" data-media="(min-width: 800px)"></span>';
    		$output.= '<span data-src="'.$sizes[9]['source'].'" data-media="(min-width: 1000px)"></span>';

    		$output.= '<!-- Fallback content for non-JS browsers. Same img src as the initial, unqualified source element. -->';
    		$output.= '<noscript>';
    		$output.= '<img src="external/imgs/small.jpg" alt="'.$atts['alt'].'">';
        	$output.= '</noscript>';

        	$output.= '</span>';

			return $output;

		} else {

			return "Call failed!";
		}
	}
}




/* plugin settings
		flickr-secret-code - from api key - 3a6ede310a398056
		api-key - a7243d919dd70c951623cc75b5b1b3f8

		
		IE8 support
*/     