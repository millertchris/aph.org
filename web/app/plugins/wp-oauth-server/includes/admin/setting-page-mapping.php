<?php
/**
 * Mapping settings for OAuth Server Data
 *
 * @author Justin Greer <justin@justin-greer.com
 */


function wo_server_mapping_options_page() {
	 $options = get_option( 'wp_oauth_server_mapping_settings' );
	?>
	<div class="wrap">
		<h2>Mapping - WordPress OAuth Server
			<small>
				(Pro)
				| <?php echo _WO()->version; ?>
			</small>
		</h2>
	<?php settings_errors(); ?>


		<form method="post" action="options.php">
	<?php settings_fields( 'wp_oauth_server_mapping_settings_group' ); ?>

			<h3>User Info Mapping</h3>
			<p class="description">
				Manage the user info mapping using the fields below. Many SSO clients expect different field values than
				what WordPress provides. <br/>
				<strong>Leave the field blank for the default parameter to be presented.</strong>
			</p>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">User ID</th>
					<td>
						<input type="text" name="wp_oauth_server_mapping_settings[id]"
							   value="<?php echo @$options['id']; ?>"
							   placeholder="ID"/>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">User Email</th>
					<td>
						<input type="text" name="wp_oauth_server_mapping_settings[user_email]"
							   value="<?php echo @$options['user_email']; ?>"
							   placeholder="user_email"/>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Username</th>
					<td>
						<input type="text" name="wp_oauth_server_mapping_settings[user_login]"
							   value="<?php echo @$options['user_login']; ?>"
							   placeholder="user_login"/>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Display Name</th>
					<td>
						<input type="text" name="wp_oauth_server_mapping_settings[display_name]"
							   value="<?php echo @$options['display_name']; ?>"
							   placeholder="display_name"/>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">User Registered</th>
					<td>
						<input type="text" name="wp_oauth_server_mapping_settings[user_registered]"
							   value="<?php echo @$options['user_registered']; ?>"
							   placeholder="user_registered"/>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">User Nicename</th>
					<td>
						<input type="text" name="wp_oauth_server_mapping_settings[user_nicename]"
							   value="<?php echo @$options['user_nicename']; ?>"
							   placeholder="user_nicename"/>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">User Status</th>
					<td>
						<input type="text" name="wp_oauth_server_mapping_settings[user_status]"
							   value="<?php echo @$options['user_status']; ?>"
							   placeholder="user_status"/>
					</td>
				</tr>
			</table>


			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ); ?>"/>
			</p>
		</form>

	</div>
	<?php
}
