<div class='reveal-modal blocking-modal welcome-modal' id='terms_of_service_modal'>
	<h4><?php echo sprintf( __( 'Welcome to Shareaholic', 'shareaholic' ) ); ?></h4>	
	<div class="content pal">
	<p style="font-size: 18px; color: #323648; line-height: 1.4em; padding-top: 24px; margin: 0;">
		<?php echo sprintf( __( '%1$sThe Most Powerful WordPress Social Plugin & Toolkit%2$s', 'shareaholic' ), '<strong>', '</strong>' ); ?>
	</p>
	<p style="font-size: 13px; color: #666; line-height: 1.8em; padding-bottom: 0;">
	<?php echo sprintf( __( 'Shareaholic provides you with a comprehensive set of marketing tools to engage with your audience, get found on social, and grow your following. The toolkit includes Award-Winning Social Share Buttons, Related Posts, Analytics, Ad Monetization, and more. Customize what is right for your website from the Shareaholic App Manager.', 'shareaholic' ) ); ?>
	</p>
	
	<div class="shr-people"></div>

	<!--
	<div class="plugin-value-section-container">
		<section class="plugin-value-section">
		<span class="plugin-value-line"></span>
		<ul class="plugin-value">
			<li class="engage">
			<span class="icon fa fa-fire"></span>
			<span class="title"><?php _e( 'Engage', 'shareaholic' ); ?></span>
			<p><?php _e( 'Increase traffic, time on site and repeat visits.', 'shareaholic' ); ?></p>
			</li>
			<li class="learn">
			<span class="icon fa fa-bar-chart-o"></span>
			<span class="title"><?php _e( 'Discover', 'shareaholic' ); ?></span>
			<p><?php _e( 'Understand your audience with our easy-to-use analytics dashboard.', 'shareaholic' ); ?></p>
			</li>
			<li class="monetize">
			<span class="icon fa fa-usd"></span>
			<span class="title"><?php _e( 'Earn', 'shareaholic' ); ?></span>
			<p><?php _e( 'Generate revenue with personalized, unobtrusive native ads.', 'shareaholic' ); ?></p>
			</li>
			<li class="acquire">
			<span class="icon fa fa-bullhorn"></span>
			<span class="title"><?php _e( 'Acquire', 'shareaholic' ); ?></span>
			<p><?php _e( 'Reach over 400 million people with native ads and promoted content.', 'shareaholic' ); ?></p>
			</li>
		</ul>
		</section>
	</div>
	-->
	
	<div class="pvl">
		<a id="get_started" onclick="document.getElementById('get_started').innerHTML='<?php echo sprintf( __( 'Setting up...', 'shareaholic' ) ); ?>';document.getElementById('get_started').classList.add('btn_main_busy');" class="btn_main" style="margin-top: -20px;"><?php echo sprintf( __( 'Get Started →', 'shareaholic' ) ); ?></a>
		<p style="margin-bottom: 20px;"><small style="font-size:12px; color: rgb(136, 136, 136);"><?php echo sprintf( __( 'By clicking "Get Started" you agree to Shareholic\'s %1$sTerms of Service%2$s and %3$sPrivacy Policy%4$s.', 'shareaholic' ), '<a href="https://www.shareaholic.com/terms/?src=wp_admin" target="_new">', '</a>', '<a href="https://www.shareaholic.com/privacy/?src=wp_admin" target="_new">', '</a>' ); ?></small>
		</p>
	</div>

	</div>
</div>
