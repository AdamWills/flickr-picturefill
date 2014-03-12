<?php

/**
 * @package   Flickr Picturefill
 * @author    Adam Wills <adam@adamwills.com>
 * @license   MIT/GPLv2
 * @link      http://adamwills.com
 * @copyright 2013 Adam Wills
 *
 * Plugin Name: Flickr Picturefill
 * Plugin URI:  http://adamwills.com/
 * Description: A plugin that combines the power of picturefill and the Flickr API to provide responsive images
 * Version:     1.0.0
 * Author:      Adam Wills
 * Author URI:  http://adamwills.com
 * Text Domain: plugin-name-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( plugin_dir_path( __FILE__ ) . 'class-flickr-picturefill.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'Flickr_Picturefill', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Flickr_Picturefill', 'deactivate' ) );

Flickr_Picturefill::get_instance();