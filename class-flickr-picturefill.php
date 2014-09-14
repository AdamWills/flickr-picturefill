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
	const VERSION = '2.1.0';

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

		// Load public-facing JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_shortcode( 'picturefill', array( $this, 'create_shortcode' ) );

		add_filter('media_upload_tabs', array( $this, 'add_tab_to_media_uploader' ) );

		add_action( 'media_upload_flickrframe', array( $this, 'display_flickr_photostream')	);

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
	public function enqueue_scripts() {
		wp_register_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/picturefill.min.js', __FILE__ ) );
		wp_enqueue_script( $this->plugin_slug . '-plugin-script' );
	}


	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		$this->plugin_screen_hook_suffix = add_options_page(
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

		$trans_name = 'flickrpf-'. $atts['id'];



		if ( false === ( $output = get_transient( $trans_name ) ) ) {

			$params = array(
				'api_key'	=> get_option('flickrpf_api_key'),
				'method'	=> 'flickr.photos.getSizes',
				'photo_id'	=> $atts['id'],
				'format'	=> 'php_serial',
			);

			$encoded_params = array();

			foreach ($params as $k => $v){
				$encoded_params[] = urlencode($k).'='.urlencode($v);
			}

			$url = "https://api.flickr.com/services/rest/?".implode('&', $encoded_params);

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			$rsp = curl_exec($curl);

			if(curl_errno($curl)) {
			    return curl_error($curl);
			}

			curl_close($curl);

			$rsp_obj = unserialize($rsp);

			if ( $rsp_obj['stat'] == 'ok' ) {

				$sizes = $rsp_obj['sizes']['size'];

	        	$output = '<picture>';
	        	$output.= '<!--[if IE 9]><video style="display: none;"><![endif]-->'; // IE9 support
				$output.= '<source srcset="'.$sizes[9]['source'].'" media="(min-width: 1000px)">';
				$output.= '<source srcset="'.$sizes[8]['source'].'" media="(min-width: 800px)">';
				$output.= '<!--[if IE 9]></video><![endif]-->'; // IE9 support
				$output.= '<img srcset="' . $sizes[4]['source'] . '" alt="' . $atts['alt'] . '">';
				$output.= '</picture>';

				set_transient( $trans_name, $output, 2 * 24 * HOUR_IN_SECONDS );

			}
		}
		
		return $output;
	}

	
	/**
	 * Add page to media upload to display Flickr Photostream
	 *
	 * @since    2.1.0
	 *
	 */
	public function add_tab_to_media_uploader( $tabs ) {
		$flickr_tab = array( 'flickrframe' => __( 'Flickr', 'flickr') );
		return array_merge( $tabs, $flickr_tab );
	}

	/**
	 * Display Flickr Photostream
	 *
	 * @since    2.1.0
	 *
	 */

	public function display_flickr_photostream() {
		media_upload_header();
		echo "Yo!";
		$output = '<input type="hidden" id="flickr_api_key" name="api_key" value="' . get_option('flickrpf_api_key') . '">';
		$output.= '<input type="hidden" id="flickr_user_id" name="flickr_userid" value="' . get_option('flickrpf_user_id') . '">';
		$output.= '<script type="text/javascript">console.log("fired");</script>';
	}

}