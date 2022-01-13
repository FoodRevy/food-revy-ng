<?php
/**
 * This file holds the ShareaholicAdmin class.
 *
 * @package shareaholic
 */

require_once SHAREAHOLIC_PATH . 'lib/plugin-deactivation-survey/deactivate-feedback-form.php';

/**
 * This class takes care of all of the admin interface.
 *
 * @package shareaholic
 */
class ShareaholicAdmin {

	const ACTIVATE_TIMESTAMP_OPTION = 'shareaholic_activate_timestamp';
	const REVIEW_DISMISS_OPTION     = 'shareaholic_review_notice';
	const REVIEW_FIRST_PERIOD       = 518400; // 6 days in seconds
	const REVIEW_LATER_PERIOD       = 7776000; // 3 months in seconds
	const REVIEW_FOREVER_PERIOD     = 63113904; // 2 years in seconds

	/**
	 * Loads before all else
	 */
	public static function admin_init() {
		ShareaholicUtilities::check_for_other_plugin();
		// workaround: http://codex.wordpress.org/Function_Reference/register_activation_hook
		if ( is_admin() && get_option( 'Activated_Plugin_Shareaholic' ) == 'shareaholic' ) {
			delete_option( 'Activated_Plugin_Shareaholic' );
			/* do stuff once right after activation */
			if ( has_action( 'wp_ajax_nopriv_shareaholic_share_counts_api' ) && has_action( 'wp_ajax_shareaholic_share_counts_api' ) ) {
				ShareaholicUtilities::share_counts_api_connectivity_check();
			}
			self::activation_redirect();
		}

		self::check_redirect_url();
		self::check_review_dismissal();
		self::check_plugin_review();

		if ( ! has_filter('shareaholic_deactivate_feedback_form_plugins', array('ShareaholicAdmin','deactivation_survey_data')) ) {
			add_filter('shareaholic_deactivate_feedback_form_plugins', array('ShareaholicAdmin','deactivation_survey_data'));
		}
	}

	/**
	 * Check review notice status for current user
	 */
	public static function check_review_dismissal() {

		global $current_user;
		$user_id = $current_user->ID;

		if ( ! is_admin() ||
		! current_user_can( 'publish_posts' ) ||
		! isset( $_GET['_wpnonce'] ) ||
		! wp_verify_nonce( $_GET['_wpnonce'], 'review-nonce' ) ||
		! isset( $_GET['shr_defer_t'] ) ||
		! isset( $_GET[ self::REVIEW_DISMISS_OPTION ] ) ) {
			return;
		}

		$the_meta_array = array(
			'dismiss_defer_period' => $_GET['shr_defer_t'],
			'dismiss_timestamp'    => time(),
		);

		update_user_meta( $user_id, self::REVIEW_DISMISS_OPTION, $the_meta_array );
	}

	/**
	 * Check if we should display the review notice
	 */
	public static function check_plugin_review() {

		global $current_user;
		$user_id = $current_user->ID;

		$show_review_notice     = false;
		$activation_timestamp   = get_site_option( self::ACTIVATE_TIMESTAMP_OPTION );
		$review_dismissal_array = get_user_meta( $user_id, self::REVIEW_DISMISS_OPTION, true );
		$dismiss_defer_period   = isset( $review_dismissal_array['dismiss_defer_period'] ) ? $review_dismissal_array['dismiss_defer_period'] : 0;
		$dismiss_timestamp      = isset( $review_dismissal_array['dismiss_timestamp'] ) ? $review_dismissal_array['dismiss_timestamp'] : time();

		if ( $dismiss_timestamp + $dismiss_defer_period <= time() ) {
			$show_review_notice = true;
		}

		if ( ! $activation_timestamp ) {
			$activation_timestamp = time();
			add_site_option( self::ACTIVATE_TIMESTAMP_OPTION, $activation_timestamp );
		}

		// display review message after a certain period of time after activation
		if ( ( time() - $activation_timestamp > self::REVIEW_FIRST_PERIOD ) && $show_review_notice == true ) {
			add_action( 'admin_notices', array( 'ShareaholicAdmin', 'display_review_notice' ) );
		}
	}

	public static function display_review_notice() {

		if ( ShareaholicUtilities::get_option( 'disable_review_notice' ) == 'on' ) {
			return;
		}

		$dismiss_forever = add_query_arg(
			array(
				self::REVIEW_DISMISS_OPTION => true,
				'shr_defer_t'               => self::REVIEW_FOREVER_PERIOD,
			)
		);

		$dismiss_forlater = add_query_arg(
			array(
				self::REVIEW_DISMISS_OPTION => true,
				'shr_defer_t'               => self::REVIEW_LATER_PERIOD,
			)
		);

		$dismiss_forever_url  = wp_nonce_url( $dismiss_forever, 'review-nonce' );
		$dismiss_forlater_url = wp_nonce_url( $dismiss_forlater, 'review-nonce' );

		echo '
      <style>
        .shareaholic-review-notice {
          background-size: contain; background-position: right bottom; background-repeat: no-repeat; background-image: url(' . plugins_url( 'assets/img/icon-256x256.png', __FILE__ ) . ');
        }
         .shareaholic-review-notice-text {
           background: rgba(255, 255, 255, 0.9); text-shadow: white 0px 0px 10px; margin-right: 8em !important;
        }
        
        @media only screen and (max-width: 782px) {
          .shareaholic-review-notice-text {
            margin-right: 12em !important;
         }
        }
        @media screen and (max-width: 580px) {
          .shareaholic-review-notice {
            background: #ffffff;
          }
          .shareaholic-review-notice-text {
            margin-right: 0 !important;
         }
        }
      </style>
      
      <script>
        function shr_openWindowReload(link, reload) {
          window.open(link, "_blank");
          document.location.href = reload;
        }
      </script>	

    <div class="notice notice-info is-dismissible shareaholic-review-notice">      
      <p class="shareaholic-review-notice-text">' . __( 'Hey there! We noticed that you have had  success using ', 'shareaholic' ) . '<a href="' . admin_url( 'admin.php?page=shareaholic-settings' ) . '">Shareaholic</a>' . __( ' — awesome! Could you please do us a BIG favor and give us a quick 5-star rating on WordPress? It would help us spread the word and boost our motivation. We would really appreciate it 🙂 ~ Your friends @ Shareaholic', 'shareaholic' ) . '
        <br />
        <br />
        <a onClick="' . "shr_openWindowReload('https://wordpress.org/support/plugin/shareaholic/reviews/?rate=5#new-post', '$dismiss_forever_url')" . '" class="button button-primary">' . __( 'Ok, you deserve it', 'shareaholic' ) . '</a> &nbsp;
        <a href="' . $dismiss_forlater_url . '">' . __( 'No, not good enough', 'shareaholic' ) . '</a> &nbsp;
        <a href="' . $dismiss_forever_url . '">' . __( 'I already did', 'shareaholic' ) . '</a>  &nbsp;
        <a href="' . $dismiss_forever_url . '">' . __( 'Dismiss', 'shareaholic' ) . '</a>
      </p>
    </div>';
	}

	/**
	 * Sends user to the Settings page on activation of Shareaholic plugin
	 */

	public static function activation_redirect() {
		// Bail if no activation redirect transient
		if ( ! get_transient( '_shr_activation_redirect' ) ) {
			return;
		}

		delete_transient( '_shr_activation_redirect' );

		// Bail if activating from multisite, network, or bulk
		if ( is_multisite() || is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'shareaholic-settings' ), admin_url( 'admin.php' ) ) );
	}


	/**
	 * Redirect to Plans
	 */
	public static function go_premium() {
		$api_key = ShareaholicUtilities::get_option( 'api_key' );
		echo <<<JQUERY
<script>
window.location = "https://www.shareaholic.com/plans?siteID=$api_key&brand=shareaholic";
</script>
JQUERY;
	}

	/**
	 * Redirection Utility (used by SDK - Badge)
	 */
	public static function check_redirect_url() {

		$redirect_url = isset( $_GET['shareaholic_redirect_url'] ) ? strtolower( $_GET['shareaholic_redirect_url'] ) : null;

		if ( $redirect_url != null ) {

			// Support redirect URLs with no scheme; default to httpS
			$parsed = parse_url( $redirect_url );
			if ( empty( $parsed['scheme'] ) ) {
				$redirect_url = 'https://' . ltrim( $redirect_url );
			}

			// exit if redirect is not to shareaholic.com
			$redirect_url_host = parse_url( $redirect_url, PHP_URL_HOST );

			if ( $redirect_url_host != 'shareaholic.com' && $redirect_url_host != 'stageaholic.com' && $redirect_url_host != 'spreadaholic.com' ) {
				wp_redirect( admin_url( 'admin.php?page=shareaholic-settings' ) );
				exit;
			}

			// Get User Email
			if ( function_exists( 'wp_get_current_user' ) ) {
				$current_user = wp_get_current_user();
				$user_email   = urlencode( $current_user->user_email );
			} else {
				$user_email = '';
			}

			// Pass verification_key only if current wp user has permission to the key
			if ( current_user_can( 'activate_plugins' ) ) {
				$verification_key = ShareaholicUtilities::get_option( 'verification_key' );
			} else {
				$verification_key = 'unauthorized';
			}

			$enriched_redirect_url = add_query_arg(
				array(
					'site_id'          => ShareaholicUtilities::get_option( 'api_key' ),
					'verification_key' => $verification_key,
					'email'            => $user_email,
					'ref'              => 'wordpress',
				),
				$redirect_url
			);

			wp_redirect( $enriched_redirect_url );
			exit;
		}
	}

	/**
	 * The function called during the admin_head action.
	 */
	public static function admin_header() {
		self::include_remote_js();
	}

	/**
	 * Load the terms of service notice that shows up
	 * at the top of the admin pages.
	 */
	public static function show_terms_of_service() {
		ShareaholicUtilities::load_template( 'terms_of_service_notice' );
	}

	/**
	 * Renders footer
	 */
	public static function show_footer() {
		ShareaholicUtilities::load_template( 'footer' );
	}

	/**
	 * Renders header
	 */
	public static function show_header() {
		$settings              = ShareaholicUtilities::get_settings();
		$settings['base_link'] = Shareaholic::URL . '/publisher_tools/' . $settings['api_key'] . '/';
		ShareaholicUtilities::load_template(
			'header',
			array(
				'settings' => $settings,
			)
		);
	}

	/**
	 * Renders Chat
	 */
	public static function include_chat() {
		ShareaholicUtilities::load_template( 'script_chat' );
	}

	/**
	 * Adds meta boxes for post and page options
	 */
	public static function add_meta_boxes() {
		$post_types = get_post_types();
		// $post_types = array( 'post', 'page', 'product' );
		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'shareaholic',
				'Shareaholic',
				array( 'ShareaholicAdmin', 'meta_box' ),
				$post_type,
				'side',
				'low'
			);
		}
	}

	/**
	 * This is the wp ajax callback for when a user
	 * checks a checkbox for a location that doesn't
	 * already have a location_id. After it has been
	 * successfully created the id needs to be stored,
	 * which is what this method does.
	 */
	public static function add_location() {
		$location = $_POST['location'];
		$app_name = $location['app_name'];

		// if location id is not numeric throw bad request
		// or user lacks permissions
		// or does not have the nonce token
		// otherwise forcibly change it to a number
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'shareaholic_add_location' ) ||
		! current_user_can( 'publish_posts' ) || ! is_numeric( $location['id'] ) ) {
			header( 'HTTP/1.1 400 Bad Request', true, 400 );
			die();
		} else {
			$location['id'] = intval( $location['id'] );
		}

		ShareaholicUtilities::update_options(
			array(
				'location_name_ids' => array(
					$app_name => array(
						$location['name'] => $location['id'],
					),
				),
				$app_name           => array(
					$location['name'] => 'on',
				),
			)
		);

		echo json_encode(
			array(
				'status' => "successfully created a new {$location['app_name']} location",
				'id'     => $location['id'],
			)
		);

		die();
	}

	/**
	 * Shows the message about failing to create an api key
	 */
	public static function failed_to_create_api_key() {
		// ShareaholicUtilities::load_template('failed_to_create_api_key');
		if ( isset( $_GET['page'] ) && preg_match( '/shareaholic-settings/', $_GET['page'] ) ) {
			ShareaholicUtilities::load_template( 'failed_to_create_api_key_modal' );
		}
	}

	/**
	 * The actual function in charge of drawing the meta boxes.
	 */
	public static function meta_box() {
		global $post;
		$settings = ShareaholicUtilities::get_settings();
		ShareaholicUtilities::load_template(
			'meta_boxes',
			array(
				'settings' => $settings,
				'post'     => $post,
			)
		);
	}

	/**
	 * This function fires when a post is saved
	 *
	 * @param int $post_id
	 */
	public static function save_post( $post_id ) {
		// WordPress does something silly where save_post is fired twice,
		// once with the id of a revision and once with the actual id. This
		// filters out revision ids (which we don't want)
		if ( ! wp_is_post_revision( $post_id ) ) {
			self::disable_post_attributes( $post_id );
		}
	}

	/**
	 * For each of the things that a user can disable or exclude per post,
	 * we iterate through and turn add the post meta, or make it false
	 * if it *used* to be true, but did not come through in $_POST
	 * (because unchecked boxes are not submitted).
	 *
	 * @param int $post_id
	 */
	private static function disable_post_attributes( $post_id ) {
		foreach ( array(
			'disable_share_buttons',
			'disable_open_graph_tags',
			'exclude_recommendations',
			'disable_recommendations',
		) as $attribute ) {
			$key = 'shareaholic_' . $attribute;
			if ( isset( $_POST['shareaholic'][ $attribute ] ) &&
			$_POST['shareaholic'][ $attribute ] == 'on' ) {
				update_post_meta( $post_id, $key, true );
			} elseif ( get_post_meta( $post_id, $key, true ) ) {
				update_post_meta( $post_id, $key, false );
			}
		}
	}

	/**
	 * Enqueue local styles and scripts for the admin panel
	 *
	 * @since 7.0.2.0
	 */
	public static function enqueue_scripts() {
		// Exclude from React powered page
		if ( isset( $_GET['page'] ) && preg_match( '/shareaholic/', $_GET['page'] ) && $_GET['page'] !== 'shareaholic-settings' ) {
			wp_enqueue_style( 'shareaholic_bootstrap_css', plugins_url( 'assets/css/bootstrap.css', __FILE__ ), false, ShareaholicUtilities::get_version() );
			wp_enqueue_script( 'shareholic_bootstrap_js', ShareaholicUtilities::get_file_url_for_environment('assets/js/bootstrap.min.js', 'src/js/bootstrap.js'), false, ShareaholicUtilities::get_version() );
		}

		if ( isset( $_GET['page'] ) && preg_match( '/shareaholic/', $_GET['page'] ) ) {
			wp_enqueue_style( 'shareaholic_common_css', plugins_url( 'assets/css/common.css', __FILE__ ), false, ShareaholicUtilities::get_version() );
			wp_enqueue_style( 'shareaholic_reveal_css', plugins_url( 'assets/css/reveal.css', __FILE__ ), false, ShareaholicUtilities::get_version() );
			wp_enqueue_style( 'shareaholic_main_css', plugins_url( 'assets/css/main.css', __FILE__ ), false, ShareaholicUtilities::get_version() );
			wp_enqueue_script( 'shareholic_jquery_custom_js', ShareaholicUtilities::get_file_url_for_environment('assets/js/jquery_custom.min.js', 'src/js/jquery_custom.js'), false, ShareaholicUtilities::get_version() );
			wp_enqueue_script( 'shareholic_jquery_ui_custom_js', ShareaholicUtilities::get_file_url_for_environment('assets/js/jquery_ui_custom.min.js', 'src/js/jquery_ui_custom.js'), array( 'shareholic_jquery_custom_js' ), ShareaholicUtilities::get_version() );
			wp_enqueue_script( 'shareholic_modified_reveal_js', ShareaholicUtilities::get_file_url_for_environment('assets/js/jquery.reveal.modified.min.js', 'src/js/jquery.reveal.modified.js'), array( 'shareholic_jquery_custom_js', 'shareholic_jquery_ui_custom_js' ), ShareaholicUtilities::get_version() );
			wp_enqueue_script( 'shareholic_main_js', ShareaholicUtilities::get_file_url_for_environment('assets/js/main.min.js', 'src/js/main.js'), false, ShareaholicUtilities::get_version() );
		}
	}

	/**
	 * Include remote styles and scripts for the admin panel.
	 *
	 * This addresses a conflict with 3rd party plugins that force modify the paths of
	 * scripts that are passed through to wp_enqueue_script
	 *
	 * @since 8.12.1
	 */
	public static function include_remote_js() {
		if ( isset( $_GET['page'] ) && preg_match( '/shareaholic/', $_GET['page'] ) ) {
			echo "\n<script src='" . ShareaholicUtilities::asset_url_admin( 'assets/pub/utilities.js' ) . '?' . ShareaholicUtilities::get_version() . "'></script>\n";
		}
	}

	/**
	 * Puts a new menu item under Settings.
	 */
	public static function admin_menu() {
		$icon_svg          = ShareaholicUtilities::get_icon_svg();
		$notification_icon = '';

		if ( ! ShareaholicUtilities::get_option( 'api_key' ) ) {
			$notification_icon = ' <span class="update-plugins count-1"><span class="plugin-count">!</span></span>';
		}

		add_menu_page(
			__( 'Shareaholic Settings', 'shareaholic' ),
			__( 'Shareaholic', 'shareaholic' ) . $notification_icon,
			'manage_options',
			'shareaholic-settings',
			array( 'ShareaholicAdmin', 'cloud_settings' ),
			$icon_svg
		);
		add_submenu_page(
			'shareaholic-settings',
			__( 'Cloud Settings', 'shareaholic' ),
			__( 'Cloud Settings', 'shareaholic' ),
			'manage_options',
			'shareaholic-settings',
			array( 'ShareaholicAdmin', 'cloud_settings' )
		);
		add_submenu_page(
			'shareaholic-settings',
			__( 'In-Page Code Blocks', 'shareaholic' ),
			__( 'In-Page Code Blocks', 'shareaholic' ),
			'manage_options',
			'shareaholic-settings-code-blocks',
			array( 'ShareaholicAdmin', 'admin_code_blocks' )
		);
		add_submenu_page(
			'shareaholic-settings',
			__( 'Plugin Settings', 'shareaholic' ),
			__( 'Plugin Settings', 'shareaholic' ),
			'manage_options',
			'shareaholic-settings-plugin',
			array( 'ShareaholicAdmin', 'plugin_settings' )
		);
		if (ShareaholicUtilities::get_option( 'api_key' )) {
			add_submenu_page(
				'shareaholic-settings',
				__( 'Upgrade', 'shareaholic' ),
				__( '<span style="color: #FCB214;">Upgrade to Premium</span>', 'shareaholic' ),
				'activate_plugins',
				'shareaholic-premium',
				array( 'ShareaholicAdmin', 'go_premium' )
			);
		}
	}

	/**
	 * Updates the information if passed in and sets save message.
	 */
	public static function admin_code_blocks() {
		
		// Fetch and save location status from cloud
		ShareaholicUtilities::location_sync_update_plugin();

		$settings = ShareaholicUtilities::get_settings();
		$action   = str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] );
		if ( isset( $_POST['already_submitted'] ) && $_POST['already_submitted'] == 'Y' &&
		check_admin_referer( $action, 'nonce_field' ) ) {
			echo "<div class='updated settings_updated'><p><strong>" . sprintf( __( 'Settings successfully saved', 'shareaholic' ) ) . '</strong></p></div>';

			/*
			* only checked check boxes are submitted, so we have to iterate
			* through the existing app locations and if they exist in the settings
			* but not in $_POST, it must have been unchecked, and it
			* should be set to 'off'
			*/
			foreach ( array( 'share_buttons', 'recommendations' ) as $app ) {
				if ( isset( $settings[ $app ] ) ) {
					foreach ( $settings[ $app ] as $location => $on ) {
						if ( ! isset( $_POST[ $app ][ $location ] ) && $on == 'on' ) {
							  $_POST[ $app ][ $location ] = 'off';
						}
					}
				}
				if ( ! isset( $_POST[ $app ] ) ) {
					$_POST[ $app ] = array();
				}
			}

			foreach ( array( 'share_buttons_display_on_excerpts', 'recommendations_display_on_excerpts' ) as $setting ) {
				if ( isset( $settings[ $setting ] ) &&
				! isset( $_POST['shareaholic'][ $setting ] ) &&
				$settings[ $setting ] == 'on' ) {
					$_POST['shareaholic'][ $setting ] = 'off';
				} elseif ( ! isset( $_POST['shareaholic'][ $setting ] ) ) {
					$_POST['shareaholic'][ $setting ] = array();
				}
			}

			// Save "Locations" related preferences
			ShareaholicUtilities::update_options(
				array(
					'share_buttons'   => $_POST['share_buttons'],
					'recommendations' => $_POST['recommendations'],
				)
			);

			// Save "Excerpts" related preferences
			if ( isset( $_POST['shareaholic']['share_buttons_display_on_excerpts'] ) ) {
				ShareaholicUtilities::update_options( array( 'share_buttons_display_on_excerpts' => $_POST['shareaholic']['share_buttons_display_on_excerpts'] ) );
			}
			if ( isset( $_POST['shareaholic']['recommendations_display_on_excerpts'] ) ) {
				ShareaholicUtilities::update_options( array( 'recommendations_display_on_excerpts' => $_POST['shareaholic']['recommendations_display_on_excerpts'] ) );
			}

			// Send location status to Shareaholic.com
			ShareaholicUtilities::location_sync_to_cloud();
			// Log event
			ShareaholicUtilities::log_event( 'UpdatedSettings' );
			// Clear WP cache
			ShareaholicUtilities::clear_cache();
		}

		/*
		* Just in case they've added new settings on shareaholic.com
		*/
		if ( ShareaholicUtilities::has_accepted_terms_of_service() ) {
			$api_key = ShareaholicUtilities::get_or_create_api_key();
			ShareaholicUtilities::get_new_location_name_ids( $api_key );
		}

		self::draw_admin_form();
		self::draw_verify_api_key();
	}

	/**
	 * The function for the Cloud Settings Tab
	 */
	public static function cloud_settings() {

		// Fetch and save location status from cloud
		ShareaholicUtilities::location_sync_update_plugin();

		if ( ! ShareaholicUtilities::has_accepted_terms_of_service() ) {
			ShareaholicUtilities::load_template(
				'terms_of_service_modal',
				array(
					'image_url' => plugins_url('/assets/img', SHAREAHOLIC_FILE),
				)
			);
		}
		
		if ( ShareaholicUtilities::has_accepted_terms_of_service() ) {
			$api_key = ShareaholicUtilities::get_or_create_api_key();
			$jwt     = self::get_publisher_token();

			if ( $jwt ) {
				ShareaholicUtilities::load_template(
					'admin',
					array(
						'jwt'     => $jwt,
						'api_key' => $api_key,
					)
				);
			} else {
				ShareaholicUtilities::load_template( 'failed_to_create_api_key_modal' );
				ShareaholicUtilities::load_template( 'script_chat' );
			}
		}
	}

	/**
	 * Gets the JWT auth for React UI
	 */
	private static function get_publisher_token() {
		$payload = array(
			'site_id'          => ShareaholicUtilities::get_option( 'api_key' ),
			'verification_key' => ShareaholicUtilities::get_option( 'verification_key' ),
		);

		$response = ShareaholicCurl::post( Shareaholic::API_URL . '/api/v3/sessions', $payload, 'json' );

		if ( $response && preg_match( '/20*/', $response['response']['code'] ) ) {
			return $response['body']['publisher_token'];
		}
		return false;
	}

	/**
	 * The function for the advanced admin section
	 */
	public static function plugin_settings() {

		// Fetch and save location status from cloud
		ShareaholicUtilities::location_sync_update_plugin();

		$settings = ShareaholicUtilities::get_settings();

		if ( ShareaholicUtilities::has_accepted_terms_of_service() ) {
			$api_key = ShareaholicUtilities::get_or_create_api_key();
		}

		$action = str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] );

		if ( ! ShareaholicUtilities::has_accepted_terms_of_service() ) {
			ShareaholicUtilities::load_template(
				'terms_of_service_modal',
				array(
					'image_url' => plugins_url('/assets/img', SHAREAHOLIC_FILE),
				)
			);
		}

		if ( isset( $_POST['reset_settings'] )
		&& $_POST['reset_settings'] == 'Y'
		&& check_admin_referer( $action, 'nonce_field' ) ) {
			ShareaholicUtilities::reset_settings();
			echo "<div class='updated settings_updated'><p><strong>"
			. sprintf( __( 'Settings successfully reset. Refresh this page to complete the reset.', 'shareaholic' ) )
			. '</strong></p></div>';
		}

		if ( isset( $_POST['already_submitted'] ) && $_POST['already_submitted'] == 'Y' &&
		check_admin_referer( $action, 'nonce_field' ) ) {
			echo "<div class='updated settings_updated'><p><strong>" . sprintf( __( 'Settings successfully saved', 'shareaholic' ) ) . '</strong></p></div>';

			foreach ( array( 'disable_og_tags', 'disable_admin_bar_menu', 'disable_review_notice', 'disable_debug_info', 'enable_user_nicename', 'disable_internal_share_counts_api' ) as $setting ) {
				if ( ! isset( $_POST['shareaholic'][ $setting ] ) ) {
					// If form value is unchecked, set to off
					$_POST['shareaholic'][ $setting ] = 'off';
				}
			}

			if ( isset( $_POST['shareaholic']['api_key'] ) && $_POST['shareaholic']['api_key'] != $api_key ) {
				ShareaholicUtilities::get_new_location_name_ids( $_POST['shareaholic']['api_key'] );
			}

			if ( isset( $_POST['shareaholic']['api_key'] ) ) {
				ShareaholicUtilities::update_options( array( 'api_key' => $_POST['shareaholic']['api_key'] ) );
			}

			if ( isset( $_POST['shareaholic']['disable_og_tags'] ) ) {
				ShareaholicUtilities::update_options( array( 'disable_og_tags' => $_POST['shareaholic']['disable_og_tags'] ) );
			}

			if ( isset( $_POST['shareaholic']['disable_admin_bar_menu'] ) ) {
				ShareaholicUtilities::update_options( array( 'disable_admin_bar_menu' => $_POST['shareaholic']['disable_admin_bar_menu'] ) );
			}

			if ( isset( $_POST['shareaholic']['disable_review_notice'] ) ) {
				ShareaholicUtilities::update_options( array( 'disable_review_notice' => $_POST['shareaholic']['disable_review_notice'] ) );
			}

			if ( isset( $_POST['shareaholic']['facebook_app_id'] ) ) {
				ShareaholicUtilities::update_options( array( 'facebook_app_id' => sanitize_text_field( $_POST['shareaholic']['facebook_app_id'] ) ) );
			}

			if ( isset( $_POST['shareaholic']['facebook_app_secret'] ) ) {
				ShareaholicUtilities::update_options( array( 'facebook_app_secret' => sanitize_text_field( $_POST['shareaholic']['facebook_app_secret'] ) ) );
			}

			if ( isset( $_POST['shareaholic']['disable_debug_info'] ) ) {
				ShareaholicUtilities::update_options( array( 'disable_debug_info' => $_POST['shareaholic']['disable_debug_info'] ) );
			}

			if ( isset( $_POST['shareaholic']['enable_user_nicename'] ) ) {
				ShareaholicUtilities::update_options( array( 'enable_user_nicename' => $_POST['shareaholic']['enable_user_nicename'] ) );
			}

			if ( isset( $_POST['shareaholic']['disable_internal_share_counts_api'] ) ) {
				ShareaholicUtilities::update_options( array( 'disable_internal_share_counts_api' => $_POST['shareaholic']['disable_internal_share_counts_api'] ) );
			}

			ShareaholicUtilities::log_event( 'UpdatedSettings' );
			// clear cache after settings update
			ShareaholicUtilities::clear_cache();
		}

		ShareaholicUtilities::load_template(
			'advanced_settings',
			array(
				'settings' => ShareaholicUtilities::get_settings(),
				'action'   => $action,
			)
		);
	}

	/**
	 * Outputs the actual html for the form
	 */
	private static function draw_admin_form() {
		$action   = str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] );
		$settings = ShareaholicUtilities::get_settings();

		if ( ! ShareaholicUtilities::has_accepted_terms_of_service() ) {
			ShareaholicUtilities::load_template(
				'terms_of_service_modal',
				array(
					'image_url' => plugins_url('/assets/img', SHAREAHOLIC_FILE),
				)
			);
		}

		ShareaholicUtilities::load_template(
			'settings',
			array(
				'shareaholic_url' => Shareaholic::URL,
				'settings'        => $settings,
				'action'          => $action,
				'share_buttons'   => ( isset( $settings['share_buttons'] ) ) ? $settings['share_buttons'] : array(),
				'recommendations' => ( isset( $settings['recommendations'] ) ) ? $settings['recommendations'] : array(),
				'directory'       => dirname( plugin_basename( __FILE__ ) ),
			)
		);
	}

	/**
	 * This function is in charge the logic for
	 * showing whatever it is we want to show a user
	 * about whether they have verified their api
	 * key or not.
	 */
	private static function draw_verify_api_key() {
		if ( ! ShareaholicUtilities::api_key_verified() ) {
			$settings         = ShareaholicUtilities::get_settings();
			$api_key          = $settings['api_key'];
			$verification_key = $settings['verification_key'];
			ShareaholicUtilities::load_template(
				'verify_api_key_js',
				array(
					'verification_key' => $verification_key,
				)
			);
		}
	}

	/**
	 * This function is in charge of determining whether to send the "get started" email
	 */
	public static function welcome_email() {
		// check whether email has been sent
		if ( ShareaholicUtilities::get_option( 'welcome_email_sent' ) != 'y' ) {
			// set flag that the email has been sent
			ShareaholicUtilities::update_options( array( 'welcome_email_sent' => 'y' ) );
			// send email
			self::send_welcome_email();
		}
	}


	/**
	 * This function is in charge of sending the "get started" email
	 */
	public static function send_welcome_email() {
		if ( function_exists( 'wp_mail' ) ) {
			$site_url             = get_bloginfo( 'url' );
			$api_key              = ShareaholicUtilities::get_option( 'api_key' );
			$payment_url          = 'https://www.shareaholic.com/user-settings/payments';
			$shr_wp_dashboard_url = esc_url( admin_url( 'admin.php?page=shareaholic-settings' ) );
			$sign_up_link         = esc_url( admin_url( 'admin.php?shareaholic_redirect_url=shareaholic.com/signup/' ) );
			$to                   = get_bloginfo( 'admin_email' );
			$subject              = 'Thank you for installing Shareaholic Plugin for WordPress!';
			$message              = "
      <p>Hi there,</p>
    
      <p>Thank you for installing Shareaholic on $site_url! You are one step closer to growing your website. Completing your set-up is easy, just follow these three easy steps and you'll be ready to go:</p>
        
      <p><strong>Step 1. Customize to your needs</strong><br /><br />
    
      Personalize the various apps (ex. Share Buttons and Related Content) to match your website design using the \"Customize\" buttons in your <a href='$shr_wp_dashboard_url'>Shareaholic App Manager in WordPress</a>, then choose where you want them to appear on your website using the checkboxes!
            
      <p><strong>Step 2: Create your free Shareaholic account</strong><br /><br />
    
      This will enable you to add more features like Analytics, Floating Share Buttons, Share Buttons for Images, Follow Buttons and more. <strong><a href='$sign_up_link'>Click here to sign-up</a></strong>, or <a href='$sign_up_link'>login to an existing Shareaholic account</a> and we'll automatically sync the plugin settings with your account.</p>
    
      <p><strong>Step 3: Control your earnings and setup how you would like to get paid</strong><br /><br />
    
      Decide how much you would like to earn from Promoted Content (native ads that appear in the Related Content app) and other monetization apps by editing your settings in the \"Monetization\" section of the plugin. Next, visit the \"Payments\" <a href='$payment_url'>section of your Shareaholic.com account</a> to add your PayPal information, so you can collect the revenue your site earns.</p>
    
      <p>Have questions? Simply reply to this email and we will help you out!</p>

      <p>Let's get started,<br /><br />
    
      The Shareaholic Team<br />
      <a href='https://support.shareaholic.com'>support.shareaholic.com</a><br /><br />
      <img width='200' height='36' src='https://www.shareaholic.com/assets/layouts/shareaholic-logo.png' alt='Shareaholic' title='Shareaholic' /><br />
      <p style='font-size:12px;color:#C3C2C2;'>This is an automated, one-time e-mail sent by your WordPress CMS directly to the website admin</p><br />
      <img width='0' height='0' src='https://www.google-analytics.com/collect?v=1&tid=UA-12964573-6&cid=$api_key&t=event&ec=email&ea=open&el=$site_url-$api_key&cs=lifecycle&cm=email&cn=wp_welcome_email' />";

			$headers  = "From: Shareaholic <hello@shareaholic.com>\r\n";
			$headers .= "Reply-To: Shareaholic <hello@shareaholic.com>\r\n";
			$headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=utf-8\r\n";

			// Send email
			// wp_mail($to, $subject, $message, $headers);
		}
	}

	/**
	 * This function adds our custom image type to the Media Library
	 */
	public static function show_custom_sizes( $sizes ) {
		return array_merge(
			$sizes,
			array(
				'shareaholic-thumbnail' => __( 'Shareaholic' ),
			)
		);
	}

	/**
	 * This function adds a notice to Settings->Permalinks
	 */
	public static function admin_notices() {
		$current_screen = get_current_screen();

		if ( $current_screen->id === 'options-permalink' ) {
			$css_class = 'notice notice-warning is-dismissible';
			$message   = 'WARNING: Updating your URL or permalink structure will reset the social share counts for your pages. <a href="https://www.shareaholic.com/plans">Upgrade Shareaholic</a> to enable <a href="https://support.shareaholic.com/hc/en-us/articles/115002083586">Share Count Recovery</a>.';
			echo "<div class='$css_class'><p style='font-weight: bold;'>";
			_e( $message, 'Shareaholic' );
			echo '</p></div>';
		}
	}

	/**
	 * Registers Shareaholic plugin for the deactivation survey library code.
	 *
	 * @param array $plugins
	 *
	 * @return array
	 */
	public static function deactivation_survey_data( $plugins ) {
		$plugin_data = get_plugin_data(SHAREAHOLIC_FILE, false, false);
		
		$plugins[] = (object) array(
			'title_slugged' => sanitize_title($plugin_data['Name']),
			'basename'    => plugin_basename(SHAREAHOLIC_FILE),
			'logo'    => plugins_url('/assets/img/icon-256x256.png', SHAREAHOLIC_FILE),
			'api_server' => 'yarpp.com',
			'script_cache_ver'    => Shareaholic::VERSION,
			'bgcolor' => '#fff',
			'send' => array(
				'plugin_name'      => 'shareaholic',
				'plugin_version'   => Shareaholic::VERSION,
				'api_key'          => ShareaholicUtilities::get_option( 'api_key' ),
				'verification_key' => ShareaholicUtilities::get_option( 'verification_key' ),
				'platform'         => 'wordpress',
				'domain'           => site_url(),
				'language'         => strtolower( get_bloginfo( 'language' ) ),
			),
			'reasons' => array(
				'error'                  => esc_html__( 'I think I found a bug', 'shareaholic' ),
				'feature-missing'        => esc_html__( 'It\'s missing a feature I need', 'shareaholic' ),
				'too-hard'               => esc_html__( 'I couldn\'t figure out how to do something', 'shareaholic' ),
				'inefficient'            => esc_html__( 'It\'s too slow or inefficient', 'shareaholic' ),
				'no-signup'              => esc_html__( 'I don\'t want to signup', 'shareaholic' ),
				'temporary-deactivation' => esc_html__( 'Temporarily deactivating or troubleshooting', 'shareaholic' ),
				'other'                  => esc_html__( 'Other', 'shareaholic' )
			),
			'reasons_needing_comment' => array(
				'error',
				'feature-missing',
				'too-hard',
				'other'
			),
			'translations' => array(
				'quick_feedback'        => esc_html__( 'Quick Feedback', 'shareaholic' ),
				'foreword'              => esc_html__( 'If you would be kind enough, please tell us why you are deactivating the plugin:',
													   'shareaholic' ),
				'please_tell_us'        => esc_html__( 'Please share anything you think might be helpful. The more we know about your problem, the faster we\'ll be able to fix it.',
													   'shareaholic' ),
				'cancel'                => esc_html__( 'Cancel', 'shareaholic' ),
				'skip_and_deactivate'   => esc_html__( 'Skip &amp; Deactivate', 'shareaholic' ),
				'submit_and_deactivate' => esc_html__( 'Submit &amp; Deactivate', 'shareaholic' ),
				'please_wait'           => esc_html__( 'Please wait...', 'shareaholic' ),
				'thank_you'             => esc_html__( 'Thank you!', 'shareaholic' ),
				'ask_for_support'       => sprintf(
					esc_html__( 'Have you visited %1$sthe support forum%2$s and %3$sread the FAQs%2$s for help?',
								'shareaholic' ),
					'<a href="https://support.shareaholic.com/" target="_blank" >',
					'</a>',
					'<a href="https://support.shareaholic.com/hc/categories/200101476-Shareaholic-WordPress-Plugin" target="_blank" >'
				),
				'email_request'         => esc_html__( 'If you would like to tell us more, please leave your email here. We will be in touch (only for product feedback, nothing else).',
													   'shareaholic' ),
			)

		);

		return $plugins;
	}
}
