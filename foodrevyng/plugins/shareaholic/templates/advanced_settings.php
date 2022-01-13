<?php ShareaholicAdmin::show_header(); ?>

<script>
window.ShareaholicConfig = {
	apiHost: "<?php echo Shareaholic::API_URL; ?>",
	serviceHost: "<?php echo Shareaholic::URL; ?>",
	assetHost: "<?php echo ShareaholicUtilities::asset_url_admin(); ?>",
	assetFolders: true,
	origin: "wp_plugin",
	language: "<?php echo strtolower( get_bloginfo( 'language' ) ); ?>"
};
</script>

<div class='wrap'>
	<h2><?php _e( 'Plugin Settings', 'shareaholic' ); ?></h2>
	<div style="margin-top:10px;"></div>

	<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
		<?php _e( 'After changing any Shareaholic plugin setting, it is good practice to clear any WordPress caching plugins like W3 Total Cache or WP Super Cache.', 'shareaholic' ); ?>
		<div class='clear'></div>
		<div class="app">
			<h2><?php echo sprintf( __( 'Getting Started' ) ); ?></h2>
			<p><?php echo sprintf( __( '%1$sLearn the basics of how to get started and configure Shareaholic through our popular WordPress plugin.%2$s', 'shareaholic' ), '<a href="https://support.shareaholic.com/hc/en-us/categories/200101476-WordPress-Plugin" target="_blank">', '</a>' ); ?> <?php echo sprintf( __( 'If you are upgrading from an earlier version of Shareaholic for WordPress and need help, have a question or have a bug to report, please %1$slet us know%2$s.', 'shareaholic' ), '<a href="https://www.shareaholic.com/help/message" target="_blank">', '</a>' ); ?>
			</p>
		</div>

		<div class='clear'></div>

		<div class="app">
			<h2><?php _e( 'Server Connectivity', 'shareaholic' ); ?></h2>
			<div class="app-content">
			<?php if ( ShareaholicUtilities::connectivity_check() == 'SUCCESS' ) { ?>
				<span class="key-status passed"><i class="fa fa-circle green" aria-hidden="true"></i> <?php _e( 'Shareaholic.com Cloud servers are reachable', 'shareaholic' ); ?></span>
				<p class="key-description"><?php _e( 'Shareaholic plugin should be working correctly.', 'shareaholic' ); ?> <?php _e( 'All Shareaholic Cloud servers are accessible.', 'shareaholic' ); ?></p>	
			<?php } else { // can't connect to any server ?>
				<span class="key-status failed"><i class="fa fa-circle red blink" aria-hidden="true"></i> <?php _e( 'Unable to reach any Shareaholic server', 'shareaholic' ); ?></span> <a href="#" onClick="window.location.reload(); this.innerHTML='<?php _e( 'Checking...', 'shareaholic' ); ?>';"><?php _e( 'Re-check', 'shareaholic' ); ?></a>
				<p class="key-description"><?php echo sprintf( __( 'A network problem or firewall is blocking connections from your web server to Shareaholic.com. <strong>Shareaholic cannot work correctly until this is fixed.</strong> Please contact your web host or firewall administrator and give them <a href="%1$s" target="_blank">this information about Shareaholic and firewalls</a>. <a href="#" class="%2$s">Let us know too</a>, so we can follow up!' ), 'https://www.shareaholic.com/blog/shareaholic-hosting-faq/', 'drift-open-chat', '</a>' ); ?></p>
			<?php } ?>

			<?php if ( ShareaholicUtilities::get_option( 'disable_internal_share_counts_api' ) == null || ShareaholicUtilities::get_option( 'disable_internal_share_counts_api' ) == 'off' ) { ?>
				<?php if ( ShareaholicUtilities::share_counts_api_connectivity_check() == 'SUCCESS' ) { ?>
				<span class="key-status passed"><i class="fa fa-circle green" aria-hidden="true"></i> <?php _e( 'Server-side Share Counts API is reachable', 'shareaholic' ); ?></span>
				<p class="key-description"><?php _e( 'The server-side Share Counts API should be working correctly.', 'shareaholic' ); ?> <?php _e( 'All servers and services needed by the API are accessible.', 'shareaholic' ); ?></p>
				<?php } else { // can't connect to any server ?>
				<span class="key-status failed"><i class="fa fa-circle red blink" aria-hidden="true"></i> <?php _e( 'Unable to reach the server-side Share Count API', 'shareaholic' ); ?></span> <a href="#" onClick="window.location.reload(); this.innerHTML='<?php _e( 'Checking...', 'shareaholic' ); ?>';"><?php _e( 'Re-check', 'shareaholic' ); ?></a>
				<p class="key-description"><?php echo sprintf( __( 'A network problem or firewall is blocking connections from your web server to various Share Count APIs. <strong>The API cannot work correctly until this is fixed.</strong> If you continue to face this issue, please <a href="#" class="%s">contact us</a> and we will follow up! In the meantime, if you disable the server-side Share Counts API from the Social Share Counts options, Shareaholic will default to using client-side APIs for share counts successfully -- so nothing to worry about!' ), 'drift-open-chat' ); ?></p>
				<?php } ?>
			<?php } ?>
			</div>
		</div>

		<div class='clear'></div>
	
		<div class="app">
			<h2><?php _e( 'Shareaholic Site Profile ID', 'shareaholic' ); ?></h2>
			<div class="app-content">
			<?php
			if ( ShareaholicUtilities::get_option( 'api_key' ) ) {
				echo '<code style="font-size: 16px;">' . ShareaholicUtilities::get_option( 'api_key' ) . '</code>';
				if (ShareaholicUtilities::site_id_is404() == '404') {
					echo '<p style="margin-left:0;"> <span class="key-status failed"><i class="fa fa-circle red blink" aria-hidden="true"></i> ';
					echo sprintf( __( 'This Site Profile ID is invalid. Try <a href="%1$s"">resetting the plugin to fix this issue</a>.', 'shareaholic' ), '#reset' );
					echo '</span></p>';
				}
			} else {
				_e( 'Not set.', 'shareaholic' );
			}
			?>
			</div>
		</div>
		
		<div class='clear'></div>
		
		<?php
		// Show this section only if we have a valid Site ID
		if (ShareaholicUtilities::site_id_is200() == '200') {
			?>
		<div class="app">
			<h2><?php _e( 'Connect Shareaholic Account', 'shareaholic' ); ?></h2>
			<div class="app-content">
			<?php echo sprintf( __( 'Connecting this plugin to a free Shareaholic account is highly recommended. Doing so will unlock additional functionality that requires authentication such as site ownership verification, team features and site integrations like Google Analytics.', 'shareaholic' ) ); ?>
			<div class="button-wrapper">
				<a href="<?php echo esc_url( admin_url( 'admin.php?shareaholic_redirect_url=shareaholic.com/signup/' ) ); ?>" target="_blank" class="btn btn-dark" role="button" style="font-size: 14px;"><?php echo sprintf( __( 'Connect Plugin', 'shareaholic' ) ); ?></a>
			</div>
			</div>
		</div>
		<?php 
		}
		?>

		<div class='clear'></div>

		<div class="app">
			<h2><?php _e( 'Plugin &harr; Shareaholic Cloud Sync', 'shareaholic' ); ?></h2>
			<div class="app-content">
				<?php
				$sync_status = ShareaholicUtilities::location_sync_status();
				if ( $sync_status == 200 ) {
					?>
					<span class="key-status passed"><i class="fa fa-circle green" aria-hidden="true"></i> <?php _e( 'Plugin In-Page Block settings are in-sync with Shareaholic Cloud', 'shareaholic' ); ?></span>
				<?php } elseif ( $sync_status == 409 ) { ?>
					<span class="key-status failed"><i class="fa fa-circle red blink" aria-hidden="true"></i> <?php _e( 'Plugin In-Page Block settings are NOT in-sync with Shareaholic Cloud.', 'shareaholic' ); ?> <a href="https://www.shareaholic.com/help/message" target="_blank"><?php _e( 'Please contact Shareaholic support for help.', 'shareaholic' ); ?></a></span>
				<?php } else { ?>
					<span class="key-status unknown"><i class="fa fa-circle grey" aria-hidden="true"></i> <?php _e( 'Cloud Sync status is unknown.', 'shareaholic' ); ?></span> <a href="https://www.shareaholic.com/help/message" target="_blank"><?php _e( 'Please contact Shareaholic support for help.', 'shareaholic' ); ?></a>
				<?php } ?>
			</div>
		</div>

		<div class='clear'></div>
		
		<form name='advanced_settings' method='post' action='<?php echo $action; ?>'>
		<?php wp_nonce_field( $action, 'nonce_field' ); ?>
		<input type='hidden' name='already_submitted' value='Y'>
			<div class='clear'>
			<div class="app">
				<h2><?php _e( 'Social Share Counts', 'shareaholic' ); ?></h2>
				<div class="app-content">
				
				<div class="shr-form-item">
					<input type='checkbox' id='user_nicename' name='shareaholic[enable_user_nicename]' class='check'
					<?php if ( isset( $settings['enable_user_nicename'] ) ) { ?>
						<?php echo ( $settings['enable_user_nicename'] == 'on' ? 'checked' : '' ); ?>
						<?php } ?>>
					<label for="user_nicename"> <?php echo sprintf( __( 'Enable <code>&percnt;author&percnt;</code> permalink tag for Share Count Recovery', 'shareaholic' ) ); ?></label>
				</div>
				
				<div class="shr-form-item">
					<input type='checkbox' id='share_counts' name='shareaholic[disable_internal_share_counts_api]' class='check'
					<?php if ( isset( $settings['disable_internal_share_counts_api'] ) ) { ?>
						<?php echo ( $settings['disable_internal_share_counts_api'] == 'on' ? 'checked' : '' ); ?>
						<?php } ?>>
						<label for="share_counts"> <?php echo sprintf( __( 'Disable Local Share Count Proxy.', 'shareaholic' ) ); ?> <?php echo sprintf( __( 'When enabled, Share Counts will be fetched and cached locally by your server. This local proxy enhances user privacy and share counts but uses your server resources. Retrieving, caching and serving Share Counts can be a server and database intensive activity which can test even the best of web hosts. You can save on your hosting bill by offloading this heavy lifting to Shareaholic Cloud servers instead, <a href="%s" target="_blank">learn more</a>.', 'shareaholic' ), 'https://support.shareaholic.com/hc/en-us/articles/360029500132' ); ?></label>
				</div>
				
				<?php if ( isset( $settings['disable_internal_share_counts_api'] ) && $settings['disable_internal_share_counts_api'] != 'on' ) { ?>
					<fieldset id='facebook-app' <?php echo ( ShareaholicUtilities::facebook_auth_check() != 'SUCCESS' ? "class='failed'" : '' ); ?>>
					<legend>
					<?php _e( 'Facebook', 'shareaholic' ); ?>
					
						<?php if ( ShareaholicUtilities::facebook_auth_check() == 'SUCCESS' ) { ?>
						<span class="key-status passed"><i class="fa fa-circle green" aria-hidden="true"></i> <?php _e( 'Live', 'shareaholic' ); ?></span>
						<?php } else { ?>
						<span class="key-status failed"><i class="fa fa-circle red blink" aria-hidden="true"></i> <?php _e( 'Missing or Invalid Credentials', 'shareaholic' ); ?></span></a>
						<?php } ?>

					</legend>

					<div class="shr-form-item shr-form-text">
					<label for="facebook_app_id"><?php _e( 'Facebook App ID', 'shareaholic' ); ?></label><br>
					<?php
					if ( isset( $settings['facebook_app_id'] ) ) {
						$facebook_app_id = trim( $settings['facebook_app_id'] );
					} else {
						$facebook_app_id = '';
					}
					?>
					<input class="regular-text" id="facebook_app_id" type="text" name='shareaholic[facebook_app_id]' value="<?php echo $facebook_app_id; ?>">
					</div>
					<div class="shr-form-item shr-form-text">
					<label for="facebook_app_secret"><?php _e( 'Facebook App Secret', 'shareaholic' ); ?></label><br>
					<?php
					if ( isset( $settings['facebook_app_id'] ) ) {
						$facebook_app_secret = trim( $settings['facebook_app_secret'] );
					} else {
						$facebook_app_secret = '';
					}
					?>
					<input class="regular-text" id="facebook_app_secret" type="text" name='shareaholic[facebook_app_secret]' value="<?php echo $facebook_app_secret; ?>">
					</div>

					<?php echo sprintf( __( 'Required for better Facebook Share Counts. Please %1$sfollow documentation%2$s to get your Facebook App ID and Secret.', 'shareaholic' ), '<a href="https://support.shareaholic.com/hc/en-us/articles/360028605231" target="_blank">', '</a>' ); ?>
				</fieldset>
				<?php } ?>
				
				<div class="button-wrapper">
					<input type='submit' class="btn btn-primary btn-medium" onclick="this.value='<?php echo sprintf( __( 'Saving Changes...', 'shareaholic' ) ); ?>';" value='<?php echo sprintf( __( 'Save Changes', 'shareaholic' ) ); ?>'>
				</div>
				</div>
			</div>
			</div>

			<div class='clear'>
			<div class="app">
				<h2><?php _e( 'Advanced', 'shareaholic' ); ?></h2>
				<div class="app-content">
				<div class="shr-form-item">
					<input type='checkbox' id='og_tags' name='shareaholic[disable_og_tags]' class='check'
					<?php if ( isset( $settings['disable_og_tags'] ) ) { ?>
						<?php echo ( $settings['disable_og_tags'] == 'on' ? 'checked' : '' ); ?>
						<?php } ?>>
					<label for="og_tags"> <?php echo sprintf( __( 'Disable <code>Open Graph</code> tags', 'shareaholic' ) ); ?> <?php echo sprintf( __( '(it is recommended NOT to disable open graph tags)', 'shareaholic' ) ); ?></label>
				</div>
				<div class="shr-form-item">
					<input type='checkbox' id='admin_bar' name='shareaholic[disable_admin_bar_menu]' class='check'
					<?php if ( isset( $settings['disable_admin_bar_menu'] ) ) { ?>
						<?php echo ( $settings['disable_admin_bar_menu'] == 'on' ? 'checked' : '' ); ?>
						<?php } ?>>
					<label for="admin_bar"> <?php echo sprintf( __( 'Disable Admin Bar Menu (requires page refresh)', 'shareaholic' ) ); ?></label>
				</div>
				<div class="shr-form-item">
					<input type='checkbox' id='review_notice' name='shareaholic[disable_review_notice]' class='check'
					<?php if ( isset( $settings['disable_review_notice'] ) ) { ?>
						<?php echo ( $settings['disable_review_notice'] == 'on' ? 'checked' : '' ); ?>
						<?php } ?>>
					<label for="review_notice"> <?php echo sprintf( __( 'Disable Review Notice', 'shareaholic' ) ); ?></label>
				</div>
				<div class="shr-form-item">
					<input type='checkbox' id='debugger' name='shareaholic[disable_debug_info]' class='check'
					<?php if ( isset( $settings['disable_debug_info'] ) ) { ?>
						<?php echo ( $settings['disable_debug_info'] == 'on' ? 'checked' : '' ); ?>
						<?php } ?>>
					<label for="debugger"> <?php echo sprintf( __( 'Disable Debugger (it is recommended NOT to disable the debugger)', 'shareaholic' ) ); ?></label>
				</div>
				<div class="button-wrapper">
					<input type='submit' class="btn btn-primary btn-medium" onclick="this.value='<?php echo sprintf( __( 'Saving Changes...', 'shareaholic' ) ); ?>';" value='<?php echo sprintf( __( 'Save Changes', 'shareaholic' ) ); ?>'>
				</div>
				</div>
			</div>
			</div> 
		</form>
		
		<?php
		if ( ShareaholicUtilities::get_option( 'api_key' ) ) {
			?>
		<div class='clear'></div>
		
		<form name='reset_settings' method='post' action='<?php echo $action; ?>'>
			<?php wp_nonce_field( $action, 'nonce_field' ); ?>
			<input type='hidden' name='reset_settings' value='Y'>
			<div class="app">
			<h2 id="reset"><?php _e( 'Reset Plugin', 'shareaholic' ); ?></h2>
			<div class="app-content">
				<?php _e( 'This will reset all of your settings and start you from scratch. This can not be undone.', 'shareaholic' ); ?>
				<div class="button-wrapper">
				<input class="btn btn-danger btn-medium" type='submit' onclick="this.value='<?php _e( 'Resetting Plugin...', 'shareaholic' ); ?>';" value='<?php _e( 'Reset Plugin', 'shareaholic' ); ?>'>
				</div>
			</div>
			</div>
			
			<?php
		}
		?>

			<div class='clear' style="padding-bottom:10px;"></div>

		</form>
		</div>
	</div>
	</div>
</div>

<?php ShareaholicAdmin::show_footer(); ?>
<?php ShareaholicAdmin::include_chat(); ?>
