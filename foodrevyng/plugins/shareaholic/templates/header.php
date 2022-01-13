<!-- Header - start -->
<div id="shr-header-container"></div>
<script class="shr-app-loader shr-app-loader__header" src="<?php echo ShareaholicUtilities::asset_url_admin( 'ui-header/loader.js' ); ?>"></script>
<!-- Header - end -->

<script>
	window.first_part_of_url = '<?php echo $settings['base_link']; ?>';
	window.verification_key = '<?php echo $settings['verification_key']; ?>';
	window.SHAREAHOLIC_PLUGIN_VERSION = '<?php echo ShareaholicUtilities::get_version(); ?>';
	window.shareaholic_add_location_nonce = '<?php echo wp_create_nonce( 'shareaholic_add_location' ); ?>';
</script>
