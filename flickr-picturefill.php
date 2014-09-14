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
 * Version:     2.0.0
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

Flickr_Picturefill::get_instance();