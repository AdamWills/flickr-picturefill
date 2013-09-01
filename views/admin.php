<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Flickr_Picturefill
 * @author    Adam Wills <adam@adamwills.com>
 * @license   GPL-2.0+
 * @link      http://adamwills.com
 * @copyright 2013 Adam Wills
 */
?>
<div class="wrap">

	<h2>Flickr Picturefill</h2>

	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>
		<?php settings_fields( 'flickr_picturefill_options' ); ?>

		<table class="form-table">

		<tr valign="top">
			<th scope="row">Flickr API Key</th>
			<td><input type="text" name="flickrpf_api_key" style="width:19em" value="<?php echo get_option('flickrpf_api_key'); ?>" /></td>
		</tr>
		 
		</table>

		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="flickrpf_api_key" />

		<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>	

	</form>
</div>