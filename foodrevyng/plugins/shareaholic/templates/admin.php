<?php if ( $api_key ) { ?>

	<?php ShareaholicAdmin::show_header(); ?>

	<div class='wrap'>
	<!-- h2 tag needed for WP to know where to place notices -->
	<h2 class="shareaholic-settings-h2"></h2>
	<script>
	window.ShareaholicConfig = {
		apiKey: "<?php echo $api_key; ?>",
		token: "<?php echo $jwt; ?>",
		apiHost: "<?php echo Shareaholic::API_URL; ?>",
		serviceHost: "<?php echo Shareaholic::URL; ?>",
		assetHost: "<?php echo ShareaholicUtilities::asset_url_admin(); ?>",
		assetFolders: true,
		origin: "wp_plugin",
		language: "<?php echo strtolower( get_bloginfo( 'language' ) ); ?>"
	};
	</script>

	<div id="root" class="shr-site-settings"></div>

	<script class="shr-app-loader__site-settings" src="<?php echo ShareaholicUtilities::asset_url_admin( 'ui-site-settings/loader.js' ); ?>"></script>
	</div>

<?php } ?>

<?php ShareaholicAdmin::include_chat(); ?>

<script src="https://dsms0mj1bbhn4.cloudfront.net/assets/pub/loader-reachable.js" async></script>
