<div class='reveal-modal blocking-modal api-key-modal' id='failed_to_create_api_key'>
	<h4><?php echo sprintf( __( 'Setup Shareaholic', 'shareaholic' ) ); ?></h4>
	<div class="content pal" style="padding:20px;">
	<p style="font-size:14px;">
		<?php if (ShareaholicUtilities::site_id_is404() == '404') { ?>
			<?php echo sprintf( __( 'There is a syncing issue between the plugin and Shareaholic Cloud servers that requires a plugin reset to fix. To reset the plugin, please visit %1$sPlugin Settings%2$s.', 'shareaholic' ), '<a href="admin.php?page=shareaholic-settings-plugin#reset">', '</a>'); ?>
		<?php } else { ?>
		
			<?php _e( 'It appears that we are having some trouble communicating with Shareaholic Cloud servers right now. This is usually temporary. Please revisit this section after a few minutes or click "retry" now.', 'shareaholic' ); ?>
	</p>
			<a id='get_started' class="btn_main" onclick="document.getElementById('get_started').innerHTML='<?php echo sprintf( __( 'Retrying...', 'shareaholic' ) ); ?>';document.getElementById('get_started').classList.add('btn_main_busy');" style="margin-top: 15px;" href=''><?php echo _e( 'Retry', 'shareaholic' ); ?></a>
			
			<br />
			<span style="font-size:12px; font-weight:normal;">
				<a href='<?php echo admin_url(); ?>'><?php _e( 'or, try again later.', 'shareaholic' ); ?></a>
			</span>
			<br /><br />
			<p style="font-size:13px; font-weight:normal;">
				<?php echo sprintf( __( 'If you continue to get this prompt for more than a few minutes, ensure that you have the latest version of the plugin installed. If you still continue to get this prompt, then check "Shareaholic Server Connectivity Status" or reset the plugin. Both are available under %1$sPlugin Settings%2$s.', 'shareaholic' ), '<a href="admin.php?page=shareaholic-settings-plugin">', '</a>' ); ?> <?php echo sprintf( __( 'If you are stuck, have a question or have a bug to report, please %1$slet us know%2$s.', 'shareaholic' ), '<a href="https://www.shareaholic.com/help/message" target="_blank">', '</a>' ); ?>
			</p>
	<?php } ?>
	</div>
</div>

