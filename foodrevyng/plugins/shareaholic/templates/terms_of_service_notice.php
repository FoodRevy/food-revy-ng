<?php if ( current_user_can( 'manage_options' ) ) { ?>
	<div class="shareaholic-wrap-container" style="background-color: #009688; margin: 5px 0px 5px -20px;">
	<div style="margin: 0px 15px 0 20px; float: left;">
		<img src="<?php echo plugins_url( '/assets/img/check.png', SHAREAHOLIC_FILE ); ?>" width="56" height="50" />
	</div>
	<div class="shareaholic-text-container" style="color: #fff; text-shadow: 0px 1px 1px rgba(0,0,0,0.2); font-size: 14px; line-height: 1.5em; height: 50px; vertical-align: middle; display: table-cell; line-height: 1.5;">
		<?php echo sprintf( __( 'You\'ve added Shareaholic - Welcome! Action required: %1$sComplete Installation &raquo;%2$s', 'shareaholic' ), '<a href="admin.php?page=shareaholic-settings" class="button-secondary" style="vertical-align: initial;">', '</a>' ); ?>
	</div>
	</div>
	<div style="clear:both;"></div>

<?php } ?>
