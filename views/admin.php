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


		<?php settings_fields( $this->plugin_slug . '_setting_group' ); ?>
		<?php do_settings_sections( $this->plugin_slug . '_setting_group' ); ?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row">Flickr API Key</th>
				<td>
					<input type="text" name="flickr_api_key" value="<?php echo get_option('flickr_api_key'); ?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Your Flickr User ID</th>
				<td><input type="text" name="flickr_user_id" value="<?php echo get_option('flickr_user_id'); ?>"></td>
			</tr>
		</table>

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>	

	</form>
</div>