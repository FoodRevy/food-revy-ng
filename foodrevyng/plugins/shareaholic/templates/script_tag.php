<!-- Shareaholic - https://www.shareaholic.com -->
<link rel='preload' href='<?php echo ShareaholicUtilities::asset_url( 'assets/pub/shareaholic.js' ); ?>' as='script'/>
<script data-no-minify='1' data-cfasync='false'>
_SHR_SETTINGS = <?php echo json_encode( $base_settings ); ?>;
</script>
<script data-no-minify='1' data-cfasync='false' src='<?php echo ShareaholicUtilities::asset_url( 'assets/pub/shareaholic.js' ); ?>' data-shr-siteid='<?php echo $api_key; ?>' async <?php echo $overrides; ?>></script>
