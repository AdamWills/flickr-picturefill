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
 * @author  Adam Wills <adam@adamwills.com>
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

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_mysettings' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . 'flickr-picturefill.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

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
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

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
	 * Register settings to be used by the plugin.
	 *
	 * @since    1.0.0
	 */
	function register_mysettings() { // whitelist options
	  $option_group = 'flickr_picturefill_options';
	  register_setting( $option_group, 'flickrpf_api_key' );
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
	 * Create shortcode to use in posts/pages
	 *
	 * @since    1.0.0
	 */
	public function create_shortcode( $atts ) {
		
		extract( shortcode_atts( array(
			'id' => '0',
			'alt' => '',
		), $atts ) );

		$transient_name = 'get_flickr_id_' + $atts['id'];

		if ( false === ( $sizes = get_transient( $transient_name ) ) ) {

			if ( false === ( $sizes = $this->get_photo_object( $atts['id'] ) ) ) {

				return "Unable to get photo from Flickr";

			}

			else {

				set_transient( $transient_name, $sizes, 24 * HOUR_IN_SECONDS );

			}

		}

		
		wp_register_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/picturefill.min.js', __FILE__ ) );
		wp_enqueue_script( $this->plugin_slug . '-plugin-script' );

		
		$output = '<div class="responsive-image"><span data-picture data-alt="'.$atts['alt'].'">';
		$output.= '<span data-src="'.$sizes[4]['source'].'"></span>';
		$output.= '<span data-src="'.$sizes[7]['source'].'" data-media="(min-width: 400px)"></span>';
		$output.= '<span data-src="'.$sizes[8]['source'].'" data-media="(min-width: 800px)"></span>';
		$output.= '<span data-src="'.$sizes[9]['source'].'" data-media="(min-width: 1000px)"></span>';

		$output.= '<!--[if (lt IE 9) & (!IEMobile)]>';
		$output.= '<span data-src="'.$sizes[8]['source'].'"></span>';
		$output.= '<![endif]-->';

		$output.= '<!-- Fallback content for non-JS browsers. Same img src as the initial, unqualified source element. -->';
		$output.= '<noscript>';
		$output.= '<img src="external/imgs/small.jpg" alt="'.$atts['alt'].'">';
    	$output.= '</noscript>';

    	$output.= '</span></div>';

		return $output;

	}

	public function get_photo_object( $id ) {
		$params = array(
			'api_key'	=> get_option( 'flickrpf_api_key' ),
			'method'	=> 'flickr.photos.getSizes',
			'photo_id'	=> $id,
			'format'	=> 'php_serial',
		);

		$encoded_params = array();

		foreach ($params as $k => $v){
			$encoded_params[] = urlencode($k).'='.urlencode($v);
		}

		$url = "http://api.flickr.com/services/rest/?".implode('&', $encoded_params);

		$rsp = file_get_contents($url);

		$rsp_obj = unserialize($rsp);

		if ($rsp_obj['stat'] == 'ok')
			return $rsp_obj['sizes']['size'];
		else
			return false;
	}
}




/* plugin settings
		flickr-secret-code - from api key - 3a6ede310a398056
		api-key - a7243d919dd70c951623cc75b5b1b3f8

		
		IE8 support
*/     