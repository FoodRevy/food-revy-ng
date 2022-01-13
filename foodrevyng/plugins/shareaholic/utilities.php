<?php
/**
 * Holds the ShareaholicUtilities class.
 *
 * @package shareaholic
 */

require_once SHAREAHOLIC_PATH . 'curl.php';
require_once SHAREAHOLIC_PATH . 'six_to_seven.php';
require_once SHAREAHOLIC_PATH . 'lib/social-share-counts/seq_share_count.php';

/**
 * This class is just a holder for general functions that have
 * no better place to be.
 *
 * @package shareaholic
 */
class ShareaholicUtilities {
	/**
	 * Logs to the PHP error log if plugin's url is set to
	 * spreadaholic or the SHAREAHOLIC_DEBUG constant is true.
	 *
	 * @param mixed $thing anything to be logged, it will be passed to `print_r`.
	 */
	public static function log( $thing ) {
		if ( preg_match( '/spreadaholic/', Shareaholic::URL ) || SHAREAHOLIC_DEBUG ) {
			error_log( print_r( $thing, true ) );
		}
	}

	/**
	 * Locate and require a template, and extract some variables
	 * to be used in that template.
	 *
	 * @param string $template  the name of the template.
	 * @param array  $vars      any variables to be extracted into the template.
	 */
	public static function load_template( $template, $vars = array() ) {
		// you cannot let locate_template to load your template
		// because WP devs made sure you can't pass
		// variables to your template :(

		$template_path = 'templates/' . $template . '.php';

		// load it
		extract( $vars );
		require $template_path;
	}

	/**
	 * Just a wrapper around get_option to
	 * get the shareaholic settings. If the settings
	 * have not been set it will return an array of defaults.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return get_option( 'shareaholic_settings', self::defaults() );
	}

	public static function reset_settings() {
		$settings = self::get_settings();
		$api_key  = self::get_option( 'api_key' );

		$response = ShareaholicCurl::post(
			Shareaholic::API_URL . '/publisher_tools/' . $api_key . '/reset/',
			$settings,
			'json'
		);

		// set the location on/off back to their defaults.
		if ( isset( $settings['location_name_ids'] ) && is_array( $settings['location_name_ids'] ) ) {
			self::set_default_location_settings( $settings['location_name_ids'] );
		}

		// Sync local locations with Cloud.
		self::location_sync_to_cloud();
	}

	/**
	 * Returns the defaults we want because PHP does not allow
	 * arrays in class constants.
	 *
	 * @return array
	 */
	private static function defaults() {
		return array(
			'disable_admin_bar_menu'              => 'on', 	// advanced.
			'disable_review_notice'               => 'off', // advanced.
			'disable_debug_info'                  => 'off', // advanced.
			'enable_user_nicename'                => 'off', // advanced.
			'disable_internal_share_counts_api'   => 'on', 	// advanced.
			'disable_og_tags'                     => 'off', // advanced.
			'api_key'                             => '',
			'verification_key'                    => '',
			'recommendations_display_on_excerpts' => 'on',
			'share_buttons_display_on_excerpts'   => 'on',
		);
	}

	/**
	 * Returns links to add to the plugin options admin page.
	 *
	 * @param  array $links
	 * @return array
	 */
	public static function admin_plugin_action_links( $links ) {
		if ( is_array( $links ) ) {
			$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=shareaholic-settings' ) ) . '">' . __( 'Settings', 'shareaholic' ) . '</a>';
			if (self::get_option( 'api_key' )) {
				$premium_link = '<a href="https://www.shareaholic.com/plans?siteID=' . self::get_option( 'api_key' ) . '&brand=shareaholic" target="_blank" rel="noopener noreferrer">' . __( 'Upgrade', 'shareaholic' ) . '</a>';
			}
			$helpdesk_link = '<a href="https://support.shareaholic.com/" target="_blank" rel="noopener noreferrer">' . __( 'Support & Documentation', 'shareaholic' ) . '</a>';

			array_unshift( $links, $settings_link );
			$links[] = $helpdesk_link;

			if (self::get_option( 'api_key' )) {
				$links[] = $premium_link;
			}
		}

		return $links;
	}


	/**
	 * Extend the admin bar
	 */

	public static function admin_bar_extended() {
		global $wp_admin_bar;

		$title = '<span class="ab-icon dashicons dashicons-share-alt" style="padding-top:6px;">' . '<span class="screen-reader-text">' . __( 'Shareaholic', 'shareaholic' ) . '</span></span>';

		if ( ! current_user_can( 'update_plugins' ) || ! is_admin_bar_showing() || self::get_option( 'disable_admin_bar_menu' ) == 'on' ) {
			return;
		}

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'wp_shareaholic_adminbar_menu',
				'title' => $title,
				'href'  => esc_url( admin_url( 'admin.php?page=shareaholic-settings' ) ),
			)
		);

		if ( ! is_admin() ) {

			if ( in_array( self::page_type(), array( 'page', 'post' ) ) ) {
				$url_link = esc_url( get_permalink() );
			} else {
				global $wp;
				$url_link = esc_url( trailingslashit( home_url( $wp->request ) ) );
			}

			$wp_admin_bar->add_menu(
				array(
					'parent' => 'wp_shareaholic_adminbar_menu',
					'id'     => 'wp_shareaholic_adminbar_submenu-sharecounts',
					'title'  => __( 'Check Share Counts', 'shareaholic' ),
					'href'   => 'https://www.shareaholic.com/sharecounter?url=' . $url_link,
					'meta'   => array( 'target' => '_blank' ),
				)
			);
		}

		$wp_admin_bar->add_menu(
			array(
				'parent' => 'wp_shareaholic_adminbar_menu',
				'id'     => 'wp_shareaholic_adminbar_submenu-settings',
				'title'  => __( 'Cloud Settings', 'shareaholic' ),
				'href'   => admin_url( 'admin.php?page=shareaholic-settings' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'parent' => 'wp_shareaholic_adminbar_menu',
				'id'     => 'wp_shareaholic_adminbar_submenu-general',
				'title'  => __( 'Shareaholic.com', 'shareaholic' ),
				'href'   => 'https://www.shareaholic.com/publisher_tools/' . self::get_option( 'api_key' ) . '/websites/edit/?verification_key=' . self::get_option( 'verification_key' ),
				'meta'   => array( 'target' => '_blank' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'parent' => 'wp_shareaholic_adminbar_menu',
				'id'     => 'wp_shareaholic_adminbar_submenu-help',
				'title'  => __( 'Support & FAQ', 'shareaholic' ),
				'href'   => 'https://support.shareaholic.com/',
				'meta'   => array( 'target' => '_blank' ),
			)
		);
	}

	/**
	 * Returns whether the user has accepted our terms of service.
	 *
	 * @return bool
	 */
	public static function has_accepted_terms_of_service() {
		return get_option( 'shareaholic_has_accepted_tos' );
	}

	/**
	 * Accepts the terms of service.
	 */
	public static function accept_terms_of_service() {
		update_option( 'shareaholic_has_accepted_tos', true );

		self::log_event( 'AcceptedToS' );

		echo '{}';

		die();
	}

	/**
	 * Wrapper for WordPress's get_option
	 *
	 * @param string $option
	 *
	 * @return mixed
	 */
	public static function get_option( $option ) {
		$settings = self::get_settings();
		return ( isset( $settings[ $option ] ) ? $settings[ $option ] : array() );
	}

	/**
	 * Wrapper for WordPress's update_option
	 *
	 * @param  array $array an array of options to update.
	 * @return bool
	 */
	public static function update_options( $array ) {
		$old_settings = self::get_settings();
		$new_settings = self::array_merge_recursive_distinct( $old_settings, $array );
		update_option( 'shareaholic_settings', $new_settings );
	}

	/**
	 * Return the current version.
	 *
	 * @return string that looks like a number
	 */
	public static function get_version() {
		return self::get_option( 'version' ) ? self::get_option( 'version' ) : get_option( 'SHRSBvNUM' );
	}

	/**
	 * Return host domain of WordPress install
	 *
	 * @return string
	 */
	public static function get_host() {
		$parse = parse_url( get_bloginfo( 'url' ) );
		return $parse['host'];
	}

	/**
	 * Set the current version, how simple.
	 *
	 * @param string $version the version you want to set.
	 */
	public static function set_version( $version ) {
		self::update_options( array( 'version' => $version ) );
	}

	/**
	 * Determines if the first argument version is less than the second
	 * argument version. A version can be up four levels, e.g. 1.1.1.1.
	 * Any versions not supplied will be zeroed.
	 *
	 * @param  string $version
	 * @param  string $comparer
	 * @return bool
	 */
	public static function version_less_than( $version, $comparer ) {
		$version_array  = explode( '.', $version );
		$comparer_array = explode( '.', $comparer );

		for ( $i = 0; $i <= 3; $i++ ) {
			// zero out unset numbers
			if ( ! isset( $version_array[ $i ] ) ) {
				$version_array[ $i ] = 0; }
			if ( ! isset( $comparer_array[ $i ] ) ) {
				$comparer_array[ $i ] = 0; }

			if ( $version_array[ $i ] < $comparer_array[ $i ] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Determines if the first argument version is less than or equal to the second
	 * argument version. A version can be up four levels, e.g. 1.1.1.1.
	 * Any versions not supplied will be zeroed.
	 *
	 * @param  string $version
	 * @param  string $comparer
	 * @return bool
	 */
	public static function version_less_than_or_equal_to( $version, $comparer ) {
		$version_array  = explode( '.', $version );
		$comparer_array = explode( '.', $comparer );

		if ( $version == $comparer || self::version_less_than( $version, $comparer ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the first argument version is greater than the second
	 * argument version. A version can be up four levels, e.g. 1.1.1.1.
	 * Any versions not supplied will be zeroed.
	 *
	 * @param  string $version
	 * @param  string $comparer
	 * @return bool
	 */
	public static function version_greater_than( $version, $comparer ) {
		$version_array  = explode( '.', $version );
		$comparer_array = explode( '.', $comparer );

		for ( $i = 0; $i <= 3; $i++ ) {
			// zero out unset numbers
			if ( ! isset( $version_array[ $i ] ) ) {
				$version_array[ $i ] = 0; }
			if ( ! isset( $comparer_array[ $i ] ) ) {
				$comparer_array[ $i ] = 0; }

			if ( $version_array[ $i ] > $comparer_array[ $i ] ) {
				return true;
			} elseif ( $version_array[ $i ] < $comparer_array[ $i ] ) {
				return false;
			}
		}
		return false;
	}

	/**
	 * Determines if the first argument version is greater than or equal to the second
	 * argument version. A version can be up four levels, e.g. 1.1.1.1.
	 * Any versions not supplied will be zeroed.
	 *
	 * @param  string $version
	 * @param  string $comparer
	 * @return bool
	 */
	public static function version_greater_than_or_equal_to( $version, $comparer ) {
		$version_array  = explode( '.', $version );
		$comparer_array = explode( '.', $comparer );

		if ( $version == $comparer || self::version_greater_than( $version, $comparer ) ) {
			return true;
		}

		return false;
	}

	/**
	 * This is the function that will perform the update.
	 */
	public static function perform_update() {

		// Set plugin defaults, if not already set
		$settings = self::get_settings();

		if ( empty( $settings['share_buttons_display_on_excerpts'] ) || ! isset( $settings['share_buttons_display_on_excerpts'] ) ) {
			self::update_options( array( 'share_buttons_display_on_excerpts' => 'on' ) );
		}
		if ( empty( $settings['recommendations_display_on_excerpts'] ) || ! isset( $settings['recommendations_display_on_excerpts'] ) ) {
			self::update_options( array( 'recommendations_display_on_excerpts' => 'on' ) );
		}

		if ( ! self::is_locked( 'perform_update' ) ) {
			self::set_lock( 'perform_update' );

			// Upgrade v6!
			if ( self::get_version() && intval( self::get_version() ) <= 6 ) {
				ShareaholicSixToSeven::update();
			}

			// Activate Shareaholic Cron job
			ShareaholicCron::activate();

			// Upgrade v8!
			if ( self::get_version() && intval( self::get_version() ) <= 8 && intval( self::get_version() ) > 7 ) {
				self::EightToNineUpdate();
			}

			// Clear site cache.
			self::clear_cache();

			// add other things that need to run on version change here.
			self::unlock( 'perform_update' );
		}
	}

	/**
	 * Return the type of page we're on as a string
	 * to use for the location in the JS
	 *
	 * @return string
	 */
	public static function page_type() {
		if ( is_front_page() || is_home() ) {
			return 'index';
		} elseif ( is_page() ) {
			return 'page';
		} elseif ( is_single() ) {
			return 'post';
		} elseif ( is_category() || is_author() || is_tag() || is_date() || is_search() ) {
			return 'category';
		}
	}

	/**
	 * Returns a base64 URL for the svg for use in the menu
	 *
	 * @param bool $base64 Whether or not to return base64'd output.
	 * @return string
	 */
	public static function get_icon_svg( $base64 = true ) {
		$svg = '<svg id="svg2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 21.51 21.49"><defs><style>.cls-1{fill:#92ce23;}.cls-2{fill:#3c9c6a;}</style></defs><title>logo</title><path id="path14" class="cls-1" d="M18.8,2.68H8.73V12.76H18.8ZM7.27,15.44a1.15,1.15,0,0,1-.86-.37,1.19,1.19,0,0,1-.35-.85v-13A1.21,1.21,0,0,1,7.27,0h13a1.21,1.21,0,0,1,1.21,1.21v13a1.21,1.21,0,0,1-1.21,1.22h-13"/><path id="path16" class="cls-2" d="M12.76,8.72H2.68V18.8H12.76ZM1.21,21.49a1.23,1.23,0,0,1-.86-.35A1.25,1.25,0,0,1,0,20.28v-13A1.21,1.21,0,0,1,1.21,6h13a1.21,1.21,0,0,1,1.21,1.21v13a1.22,1.22,0,0,1-1.21,1.21h-13"/><path id="path18" class="cls-1" d="M18.8,12.76H6.06v1.46a1.19,1.19,0,0,0,.35.85,1.15,1.15,0,0,0,.86.37h13a1.21,1.21,0,0,0,1.21-1.22V12.76H18.8"/></svg>';

		if ( $base64 ) {
			return 'data:image/svg+xml;base64,' . base64_encode( $svg );
		}
		return $svg;
	}

	/**
	 * Returns the appropriate asset path for environment
	 *
	 * @param string $asset
	 * @return string
	 */
	public static function asset_url( $asset = null ) {
		$env = self::get_env();
		if ( $env === 'development' ) {
			return 'http://spreadaholic.com:8080/' . $asset;
		} elseif ( $env === 'staging' ) {
			return '//d2062rwknz205x.cloudfront.net/' . $asset;
		} else {
			return '//cdn.shareaholic.net/' . $asset;
		}
	}

	public static function get_env() {
		if ( preg_match( '/spreadaholic/', Shareaholic::URL ) ) {
			return 'development';
		} elseif ( preg_match( '/stageaholic/', Shareaholic::URL ) ) {
			return 'staging';
		} else {
			return 'production';
		}
	}

	/**
	 * Returns the appropriate asset path for environment - admin
	 *
	 * @param string $asset
	 * @return string
	 */
	public static function asset_url_admin( $asset = null ) {
		if ( preg_match( '/spreadaholic/', Shareaholic::URL ) ) {
			return 'http://spreadaholic.com:8080/' . $asset;
		} elseif ( preg_match( '/stageaholic/', Shareaholic::URL ) ) {
			return 'https://d2062rwknz205x.cloudfront.net/' . $asset;
		} else {
			return 'https://cdn.shareaholic.net/' . $asset;
		}
	}

	/**
	 * Given a minified path, and a non-minified path, will return
	 * a minified or non-minified file URL based on whether SCRIPT_DEBUG is set true or not.
	 *
	 * @param string $minified_path     minified path.
	 * @param string $non_minified_path non-minified path.
	 * @return string The URL to the file.
	 */
	public static function get_file_url_for_environment( $minified_path, $non_minified_path ) {
		$script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		if ( true === $script_debug ) {
			$path = plugins_url( $non_minified_path, SHAREAHOLIC_FILE );
		} elseif ( false === $script_debug ) {
			$path = plugins_url( $minified_path, SHAREAHOLIC_FILE );
		} else {
			// This should work in any case.
			$path = plugins_url( $non_minified_path, SHAREAHOLIC_FILE );
		}
		return $path;
	}

	/**
	 * Checks whether the api key has been verified
	 * using the rails endpoint. Once the key has
	 * been verified, we store that away so that we
	 * don't have to check again.
	 *
	 * @return bool
	 */
	public static function api_key_verified() {
		$settings = self::get_settings();
		if ( isset( $settings['api_key_verified'] ) && $settings['api_key_verified'] ) {
			return true;
		}

		$api_key = $settings['api_key'];
		if ( ! $api_key ) {
			return false;
		}

		$response = ShareaholicCurl::get( Shareaholic::API_URL . '/publisher_tools/' . $api_key . '/verified' );
		$result   = $response['body'];

		if ( $result == 'true' ) {
			self::update_options(
				array(
					'api_key_verified' => true,
				)
			);
		}
	}

	/**
	 * A wrapper function to specifically update the location name ids
	 *
	 * @todo Determine whether needed anymore
	 *
	 * @param array $array an array of location names to location ids
	 * @return bool
	 */
	public static function update_location_name_ids( $array ) {
		$settings                      = self::get_settings();
		$location_name_ids             = ( isset( $settings['location_name_ids'] ) ? $settings['location_name_ids'] : array() );
		$merge                         = array_merge( $location_name_ids, $array );
		$settings['location_name_ids'] = $merge;

		update_option( 'shareaholic_settings', $settings );
	}


	/**
	 *
	 * Loads the locations names and their respective ids for an api key
	 * and sets them in the shareaholic settings.
	 *
	 * @param string $api_key
	 */
	public static function get_new_location_name_ids( $api_key ) {
		
		$response = ShareaholicCurl::get( Shareaholic::API_URL . "/publisher_tools/{$api_key}.json" );

		if ( is_array( $response ) && array_key_exists( 'body', $response ) ) {
			$response_code = wp_remote_retrieve_response_code( $response );
			if ( $response_code == '404' ) {
				self::load_template( 'failed_to_create_api_key_modal' );
				self::log_bad_response( 'FailedToFetchPubConfig_404', $response );
				return;
			}
			
			$publisher_configuration = $response['body'];
			$result                  = array();
		}

		if ( $publisher_configuration && is_array( $publisher_configuration ) ) {
			foreach ( array( 'share_buttons', 'recommendations' ) as $app ) {
				foreach ( $publisher_configuration['apps'][ $app ]['locations'] as $id => $location ) {
					$result[ $app ][ $location['name'] ] = $id;
				}
			}

			self::update_location_name_ids( $result );
		} else {
			self::load_template( 'failed_to_create_api_key_modal' );
			self::log_bad_response( 'FailedToFetchPubConfig', $response );
		}
	}

	/**
	 * A general function to underscore a CamelCased string.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function underscore( $string ) {
		return strtolower( preg_replace( '/([a-z])([A-Z])', '$1_$2', $string ) );
	}

	/**
	 * Passed an array of location names mapped to ids per app.
	 *
	 * @param array $array
	 */
	public static function turn_on_locations( $array, $turn_off_array = array() ) {

		if ( is_array( $array ) ) {
			foreach ( $array as $app => $ids ) {
				if ( is_array( $ids ) ) {
					foreach ( $ids as $name => $id ) {
						self::update_options(
							array(
								$app => array( $name => 'on' ),
							)
						);
					}
				}
			}
		}

		if ( is_array( $turn_off_array ) ) {
			foreach ( $turn_off_array as $app => $ids ) {
				if ( is_array( $ids ) ) {
					foreach ( $ids as $name => $id ) {
						self::update_options(
							array(
								$app => array( $name => 'off' ),
							)
						);
					}
				}
			}
		}
	}

	/**
	 * Give back only the request keys from an array. The first
	 * argument is the array to be sliced, and after that it can
	 * either be a variable-length list of keys or one array of keys.
	 *
	 * @param  array $array
	 * @param  Mixed ... can be either one array or many keys
	 * @return array
	 */
	public static function associative_array_slice( $array ) {
		$keys = array_slice( func_get_args(), 1 );
		if ( func_num_args() == 2 && is_array( $keys[0] ) ) {
			$keys = $keys[0];
		}

		$result = array();

		foreach ( $keys as $key ) {
			$result[ $key ] = $array[ $key ];
		}

		return $result;
	}

	/**
	 * Sets a lock (mutex)
	 *
	 * @param string $name
	 */
	public static function set_lock( $name ) {
		update_option( 'shareaholic_' . $name, true );
	}

	/**
	 * Checks if an action is locked.
	 *
	 * @param  string $name
	 * @return bool
	 */
	public static function is_locked( $name ) {
		return get_option( 'shareaholic_' . $name, false );
	}

	/**
	 * Unlocks a mutex
	 *
	 * @param string $name
	 */
	public static function unlock( $name ) {
		delete_option( 'shareaholic_' . $name );
	}

	/**
	 * Clears all mutex
	 */
	public static function delete_mutex() {
		delete_option( 'shareaholic_get_or_create_api_key' );
		delete_option( 'shareaholic_perform_update' );
	}

	/**
	 * Returns Active Plugins
	 */
	public static function get_active_plugins() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$network_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins  = array_merge( $active_plugins, array_keys( $network_plugins ) );
		}

		return ( isset( $active_plugins ) ? array_values( array_unique( $active_plugins ) ) : array() );
	}

	/**
	 * Checks whether a plugin is active
	 *
	 * @param string $name
	 */
	public static function check_for_other_plugin() {
		if ( is_plugin_active( 'shareaholic/shareaholic.php' ) ) {
			deactivate_plugins( 'sexybookmarks/shareaholic.php' );
		}
		if ( is_plugin_active( 'shareaholic/sexy-bookmarks.php' ) ) {
			deactivate_plugins( 'sexybookmarks/sexy-bookmarks.php' );
		}
	}

	/**
	 * Deletes the api key
	 */
	public static function delete_api_key() {
		$payload  = array(
			'site_id'          => self::get_option( 'api_key' ),
			'verification_key' => self::get_option( 'verification_key' ),
		);
		$response = ShareaholicCurl::post(
			Shareaholic::API_URL . '/integrations/plugin/delete',
			$payload,
			'json',
			true
		);
	}

	/**
	 * Returns Stats
	 */
	public static function get_stats() {
		global $wpdb;

		$stats = array(
			'posts_total' => $wpdb->get_var( "SELECT count(ID) FROM $wpdb->posts where post_type = 'post' AND post_status = 'publish'" ),
			'pages_total' => $wpdb->get_var( "SELECT count(ID) FROM $wpdb->posts where post_type = 'page' AND post_status = 'publish'" ),
			'users_total' => $wpdb->get_var( "SELECT count(ID) FROM $wpdb->users" ),
		);

		return ( isset( $stats ) ? $stats : array() );
	}

	/**
	 * Returns Local Share Count Proxy Status
	 */
	public static function get_internal_share_counts_api_status() {
		if ( self::get_option( 'disable_internal_share_counts_api' ) == null || self::get_option( 'disable_internal_share_counts_api' ) == 'off' ) {
			$server_side_share_count_status = 'on';
		} else {
			$server_side_share_count_status = 'off';
		}
		return $server_side_share_count_status;
	}

	/**
	 * Returns the api key or creates a new one.
	 *
	 * It first checks the database. If the key is not
	 * found (or is an empty string or empty array or
	 * anything that evaluates to false) then we will
	 * attempt to make a new one by POSTing to the
	 * anonymous configuration endpoint. That action
	 * is wrapped in a mutex to keep two requests from
	 * trying to create new api keys at the same time.
	 *
	 * Note: this function is called on every pageload.
	 * So please keep it as fast as possible.
	 *
	 * @return string
	 */
	public static function get_or_create_api_key() {
		$api_key = self::get_option( 'api_key' );

		// ensure api key set is atleast 30 characters
		if ( $api_key && ( strlen( $api_key ) > 30 ) ) {
			return $api_key;
		}

		if ( ! self::is_locked( 'get_or_create_api_key' ) ) {
			self::set_lock( 'get_or_create_api_key' );

			$old_settings = self::get_settings();

			delete_option( 'shareaholic_settings' );

			// restore any old settings that should be preserved between resets
			if ( isset( $old_settings['share_counts_connect_check'] ) ) {
				self::update_options(
					array(
						'share_counts_connect_check' => $old_settings['share_counts_connect_check'],
					)
				);
			}

			$verification_key = md5( mt_rand() );

			$turned_on_share_buttons_locations  = self::get_default_sb_on_locations();
			$turned_off_share_buttons_locations = self::get_default_sb_off_locations();

			$turned_on_recommendations_locations  = self::get_default_rec_on_locations();
			$turned_off_recommendations_locations = self::get_default_rec_off_locations();

			$share_buttons_attributes   = array_merge( $turned_on_share_buttons_locations, $turned_off_share_buttons_locations );
			$recommendations_attributes = array_merge( $turned_on_recommendations_locations, $turned_off_recommendations_locations );
			$data                       = array(
				'configuration_publisher' => array(
					'verification_key'           => $verification_key,
					'site_name'                  => self::site_name(),
					'domain'                     => self::site_url(),
					'platform_id'                => '12',
					'language_id'                => self::site_language(),
					'shortener'                  => 'shrlc',
					'recommendations_attributes' => array(
						'locations_attributes' => $recommendations_attributes,
					),
					'share_buttons_attributes'   => array(
						'locations_attributes' => $share_buttons_attributes,
					),
				),
			);

			$response = ShareaholicCurl::post(
				Shareaholic::API_URL . '/publisher_tools/anonymous',
				$data,
				'json'
			);

			if ( $response && preg_match( '/20*/', $response['response']['code'] ) ) {
				self::update_options(
					array(
						'api_key'           => $response['body']['api_key'],
						'verification_key'  => $verification_key,
						'location_name_ids' => $response['body']['location_name_ids'],
					)
				);

				if ( isset( $response['body']['location_name_ids'] ) && is_array( $response['body']['location_name_ids'] ) ) {
					  self::set_default_location_settings( $response['body']['location_name_ids'] );

					  ShareaholicAdmin::welcome_email();
					  self::clear_cache();
				} else {
					self::log_bad_response( 'FailedToCreateApiKey', $response );
				}
			} else {
				// add_action('admin_notices', array('ShareaholicAdmin', 'failed_to_create_api_key'));
				self::log_bad_response( 'FailedToCreateApiKey', $response );
			}

			self::unlock( 'get_or_create_api_key' );
		} else {
			usleep( 100000 );
			self::get_or_create_api_key();
		}
	}

	/**
	 * Get share buttons locations that should be turned on by default
	 *
	 * @return {Array}
	 */
	public static function get_default_sb_on_locations() {
		return array(
			array(
				'name'    => 'post_below_content',
				'counter' => 'badge-counter',
				'enabled' => true,
			),
			array(
				'name'    => 'page_below_content',
				'counter' => 'badge-counter',
				'enabled' => true,
			),
			array(
				'name'    => 'index_below_content',
				'counter' => 'badge-counter',
				'enabled' => true,
			),
			array(
				'name'    => 'category_below_content',
				'counter' => 'badge-counter',
				'enabled' => true,
			),
		);
	}

	/**
	 * Get share buttons locations that should be turned off by default
	 *
	 * @return {Array}
	 */
	public static function get_default_sb_off_locations() {
		return array(
			array(
				'name'    => 'post_above_content',
				'counter' => 'badge-counter',
				'enabled' => false,
			),
			array(
				'name'    => 'page_above_content',
				'counter' => 'badge-counter',
				'enabled' => false,
			),
			array(
				'name'    => 'index_above_content',
				'counter' => 'badge-counter',
				'enabled' => false,
			),
			array(
				'name'    => 'category_above_content',
				'counter' => 'badge-counter',
				'enabled' => false,
			),
		);
	}

	/**
	 * Get recommendations locations that should be turned on by default
	 *
	 * @return {Array}
	 */
	public static function get_default_rec_on_locations() {
		return array(
			array(
				'name'    => 'post_below_content',
				'enabled' => true,
			),
			array(
				'name'    => 'page_below_content',
				'enabled' => true,
			),
		);
	}

	/**
	 * Get recommendations locations that should be turned off by default
	 *
	 * @return {Array}
	 */
	public static function get_default_rec_off_locations() {
		return array(
			array(
				'name'    => 'index_below_content',
				'enabled' => false,
			),
			array(
				'name'    => 'category_below_content',
				'enabled' => false,
			),
		);
	}

	/**
	 * Given an object, set the default on/off locations
	 * for share buttons and recommendations
	 */
	public static function set_default_location_settings( $location_name_ids ) {
		$turned_on_share_buttons_locations  = self::get_default_sb_on_locations();
		$turned_off_share_buttons_locations = self::get_default_sb_off_locations();

		$turned_on_recommendations_locations  = self::get_default_rec_on_locations();
		$turned_off_recommendations_locations = self::get_default_rec_off_locations();

		$turned_on_share_buttons_keys = array();
		foreach ( $turned_on_share_buttons_locations as $loc ) {
			$turned_on_share_buttons_keys[] = $loc['name'];
		}

		$turned_on_recommendations_keys = array();
		foreach ( $turned_on_recommendations_locations as $loc ) {
			$turned_on_recommendations_keys[] = $loc['name'];
		}

		$turned_off_share_buttons_keys = array();
		foreach ( $turned_off_share_buttons_locations as $loc ) {
			$turned_off_share_buttons_keys[] = $loc['name'];
		}

		$turned_off_recommendations_keys = array();
		foreach ( $turned_off_recommendations_locations as $loc ) {
			$turned_off_recommendations_keys[] = $loc['name'];
		}

		$turn_on = array(
			'share_buttons'   => self::associative_array_slice( $location_name_ids['share_buttons'], $turned_on_share_buttons_keys ),
			'recommendations' => self::associative_array_slice( $location_name_ids['recommendations'], $turned_on_recommendations_keys ),
		);

		$turn_off = array(
			'share_buttons'   => self::associative_array_slice( $location_name_ids['share_buttons'], $turned_off_share_buttons_keys ),
			'recommendations' => self::associative_array_slice( $location_name_ids['recommendations'], $turned_off_recommendations_keys ),
		);

		self::turn_on_locations( $turn_on, $turn_off );
	}

	/**
	 * Log reasons for a failure of a response.
	 *
	 * Checks if the code is not a 20*, the response body
	 * is not an array, and whether the response object
	 * was false. Sends the appropriate logging message.
	 *
	 * @param string $name     the name of the event to log
	 * @param mixed  $response the response object
	 */
	public static function log_bad_response( $name, $response ) {
		if ( $response && is_array( $response ) && ! preg_match( '/20*/', $response['response']['code'] ) ) {
			self::log_event( $name, array( 'reason' => 'the response was a ' . $response['response']['code'] ) );
		} elseif ( $response && ! is_array( $response ) ) {
			$thing = preg_replace( '/\n/', '', var_export( $response, true ) );
			self::log_event( $name, array( 'reason' => 'the publisher configuration was not an array, it was this ' . $thing ) );
		}
	}

	/**
	 * Returns the site's url stripped of protocol.
	 *
	 * @return string
	 */
	public static function site_url() {
		return preg_replace( '/https?:\/\//', '', site_url() );
	}

	/**
	 * Returns the site's name
	 *
	 * @return string
	 */
	public static function site_name() {
		return get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : site_url();
	}

	/**
	 * Returns the site's primary locale / language
	 *
	 * @return string
	 */
	public static function site_language() {
		$site_language = strtolower( get_bloginfo( 'language' ) );

		if ( strpos( $site_language, 'en-' ) !== false ) {
			$language_id = 9; // English!
		} elseif ( strpos( $site_language, 'da-' ) !== false ) {
			$language_id = 7; // Danish!
		} elseif ( strpos( $site_language, 'de-' ) !== false ) {
			$language_id = 13; // German!
		} elseif ( strpos( $site_language, 'es-' ) !== false ) {
			$language_id = 31; // Spanish!
		} elseif ( strpos( $site_language, 'fr-' ) !== false ) {
			$language_id = 12; // French!
		} elseif ( strpos( $site_language, 'pt-' ) !== false ) {
			$language_id = 25; // Portuguese!
		} elseif ( strpos( $site_language, 'it-' ) !== false ) {
			$language_id = 18; // Italian!
		} elseif ( strpos( $site_language, 'zh-cn' ) !== false ) {
			$language_id = 3; // Chinese (Simplified)!
		} elseif ( strpos( $site_language, 'zh-tw' ) !== false ) {
			$language_id = 4; // Chinese (Traditional)!
		} elseif ( strpos( $site_language, 'ja-' ) !== false ) {
			$language_id = 19; // Japanese!
		} elseif ( strpos( $site_language, 'ar-' ) !== false ) {
			$language_id = 1; // Arabic!
		} elseif ( strpos( $site_language, 'sv-' ) !== false ) {
			$language_id = 32; // Swedish!
		} elseif ( strpos( $site_language, 'tr-' ) !== false ) {
			$language_id = 34; // Turkish!
		} elseif ( strpos( $site_language, 'el-' ) !== false ) {
			$language_id = 14; // Greek!
		} elseif ( strpos( $site_language, 'nl-' ) !== false ) {
			$language_id = 8; // Dutch!
		} elseif ( strpos( $site_language, 'pl-' ) !== false ) {
			$language_id = 24; // Polish!
		} elseif ( strpos( $site_language, 'ru-' ) !== false ) {
			$language_id = 27; // Russian!
		} elseif ( strpos( $site_language, 'cs-' ) !== false ) {
			$language_id = 6; // Czech!
		} else {
			$language_id = null;
		}
		return $language_id;
	}

	/**
	 * Shockingly the built in PHP array_merge_recursive function is stupid.
	 * this is stolen from the PHP docs and will overwrite existing keys instead
	 * of appending the values.
	 *
	 * http://www.php.net/manual/en/function.array-merge-recursive.php#92195
	 *
	 * @param  array $array1
	 * @param  array $array2
	 * @return array
	 */
	public static function array_merge_recursive_distinct( array &$array1, array &$array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) && isset( $merged [ $key ] ) && is_array( $merged [ $key ] ) ) {
				if ( empty( $value ) ) {
					$merged[ $key ] = array();
				} else {
					$merged [ $key ] = self::array_merge_recursive_distinct( $merged [ $key ], $value );
				}
			} else {
				$merged [ $key ] = $value;
			}
		}

		return $merged;
	}

	/**
	 * Array casting an object is not recursive, this makes it recursive
	 *
	 * @param object $d
	 *
	 * http://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/
	 */
	public static function object_to_array( $d ) {
		if ( is_object( $d ) ) {
			// Gets the properties of the given object
			// with get_object_vars function
			$d = get_object_vars( $d );
		}

		if ( is_array( $d ) ) {
			/*
			* Return array converted to object
			*/
			return array_map( array( 'self', 'object_to_array' ), $d );
		} else {
			// Return array
			return $d;
		}
	}

	/**
	 * Wrapper for the Shareaholic Content Manager Single Page worker API
	 *
	 * @param string $post_id
	 */
	public static function notify_content_manager_singlepage( $post = null, $status = null ) {

		if ( $post == null ) {
			return;
		}

		$api_key = self::get_option( 'api_key' );

		if ( self::has_accepted_terms_of_service() && ! empty( $api_key ) ) {

			if ( $status != null ) {
				$visiblity = $status;
			} else {
				$visiblity = $post->post_status;
			}

			if ( in_array( $post->post_status, array( 'draft', 'pending' ) ) ) {
				// Get the correct permalink for a draft
				$my_post              = clone $post;
				$my_post->post_status = 'published';
				$my_post->post_name   = sanitize_title( $my_post->post_name ? $my_post->post_name : $my_post->post_title, $my_post->ID );
				$post_permalink       = get_permalink( $my_post );
			} else {
				$post_permalink = get_permalink( $post->ID );
			}

			if ( $post_permalink != null
			&& ( strpos( $post_permalink, '__trashed' ) == false )
			&& ! in_array( $post->post_status, array( 'auto-draft', 'inherit' ) ) ) {
				$cm_single_page_job_url = Shareaholic::CM_API_URL . '/jobs/uber_single_page';
				$payload                = array(
					'args'             => array(
						$post_permalink,
						array(
							'force' => true,
						),
					),
					'api_key'          => self::get_option( 'api_key' ),
					'verification_key' => self::get_option( 'verification_key' ),
					'admin_ajax_path'  => admin_url( 'admin-ajax.php' ),
					'wp_version'       => Shareaholic::VERSION,
					'post_visibility'  => $visiblity,
				);
				$response               = ShareaholicCurl::post( $cm_single_page_job_url, $payload, 'json' );
			}
		}
	}

	/**
	 * Wrapper for the Shareaholic Content Manager Single Domain worker API
	 */
	public static function notify_content_manager_singledomain() {
		$domain = get_bloginfo( 'url' );
		if ( $domain != null ) {
			$cm_single_domain_job_url = Shareaholic::CM_API_URL . '/jobs/single_domain';
			$payload                  = array(
				'args'             => array(
					$domain,
					array(
						'force' => true,
					),
				),
				'api_key'          => self::get_option( 'api_key' ),
				'verification_key' => self::get_option( 'verification_key' ),
				'admin_ajax_path'  => admin_url( 'admin-ajax.php' ),
				'wp_version'       => Shareaholic::VERSION,
			);
			$response                 = ShareaholicCurl::post( $cm_single_domain_job_url, $payload, 'json' );
		}
	}

	/**
	 * This is a wrapper for the Event API
	 *
	 * @param string $event_name    the name of the event
	 * @param array  $extra_params  any extra data points to be included
	 */
	public static function log_event( $event_name = 'Default', $extra_params = false ) {
		$event_metadata = array(
			'plugin_version' => Shareaholic::VERSION,
			'api_key'        => self::get_option( 'api_key' ),
			'domain'         => get_bloginfo( 'url' ),
			'diagnostics'    => array(
				'php_version' => phpversion(),
				'wp_version'  => get_bloginfo( 'version' ),
				'theme'       => get_option( 'template' ),
				'multisite'   => is_multisite(),
			),
			'features'       => array(
				'share_buttons'   => self::get_option( 'share_buttons' ),
				'recommendations' => self::get_option( 'recommendations' ),
			),
		);

		if ( $extra_params ) {
			$event_metadata = array_merge( $event_metadata, $extra_params );
		}

		$event_api_url = Shareaholic::API_URL . '/api/events';
		$event_params  = array(
			'name' => 'WordPress:' . $event_name,
			'data' => json_encode( $event_metadata ),
		);
		$response      = ShareaholicCurl::post( $event_api_url, $event_params, '', true, 2 );
	}

	/**
	 * This is a wrapper for the Heartbeat API
	 */
	public static function heartbeat() {
		$data = array(
			'platform'          => 'wordpress',
			'plugin_name'       => 'shareaholic',
			'plugin_version'    => Shareaholic::VERSION,
			'api_key'           => self::get_option( 'api_key' ),
			'verification_key'  => self::get_option( 'verification_key' ),
			'domain'            => preg_replace( '#^https?://#', '', get_bloginfo( 'url' ) ),
			'language'          => get_bloginfo( 'language' ),
			'stats'             => self::get_stats(),
			'diagnostics'       => array(
				'tos_status'                            => self::has_accepted_terms_of_service(),
				'shareaholic_server_reachable'          => self::connectivity_check(),
				'server_side_share_count_api_reachable' => self::share_counts_api_connectivity_check(),
				'php_version'                           => phpversion(),
				'wp_version'                            => get_bloginfo( 'version' ),
				'theme'                                 => get_option( 'template' ),
				'multisite'                             => is_multisite(),
				'plugins'                               => array(
					'active' => self::get_active_plugins(),
				),
			),
			'endpoints'         => array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			),
			'advanced_settings' => array(
				'server_side_share_count_api'         => self::get_internal_share_counts_api_status(),
				'facebook_access_token'               => self::fetch_fb_access_token() === false ? 'no' : 'yes',
				'facebook_auth_check'                 => self::facebook_auth_check(),
				'enable_user_nicename'                => self::get_option( 'enable_user_nicename' ),
				'disable_og_tags'                     => self::get_option( 'disable_og_tags' ),
				'disable_admin_bar_menu'              => self::get_option( 'disable_admin_bar_menu' ),
				'recommendations_display_on_excerpts' => self::get_option( 'recommendations_display_on_excerpts' ),
				'share_buttons_display_on_excerpts'   => self::get_option( 'share_buttons_display_on_excerpts' ),
			),
		);

		$heartbeat_api_url = Shareaholic::API_URL . '/api/plugin_heartbeats';
		$response          = ShareaholicCurl::post( $heartbeat_api_url, $data, 'json', true, 2 );
	}

	/**
	 * This loads the locales
	 */
	public static function localize() {
		load_plugin_textdomain( 'shareaholic', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Function to return a list of permalink keywords
	 *
	 * @return list of keywords for the given permalink in an array
	 */
	public static function permalink_keywords( $post_id = null ) {
		global $post;
		$keywords = '';

		if ( $post_id != null ) {
			$id = $post_id;
		} else {
			$id = $post->ID;
		}

		// Get post tags
		$keywords = implode( ', ', wp_get_post_tags( $id, array( 'fields' => 'names' ) ) );

		// Support for "All in One SEO Pack" plugin keywords
		if ( get_post_meta( $id, '_aioseop_keywords' ) != null ) {
			$keywords .= ', ' . stripslashes( get_post_meta( $id, '_aioseop_keywords', true ) );
		}

		// Support for "WordPress SEO by Yoast" plugin keywords
		if ( get_post_meta( $id, '_yoast_wpseo_focuskw' ) != null ) {
			$keywords .= ', ' . stripslashes( get_post_meta( $id, '_yoast_wpseo_focuskw', true ) );
		}

		if ( get_post_meta( $id, '_yoast_wpseo_metakeywords' ) != null ) {
			$keywords .= ', ' . stripslashes( get_post_meta( $id, '_yoast_wpseo_metakeywords', true ) );
		}

		// Support for "Add Meta Tags" plugin keywords
		if ( get_post_meta( $id, '_amt_keywords' ) != null ) {
			$keywords .= ', ' . stripslashes( get_post_meta( $id, '_amt_keywords', true ) );
		}

		if ( get_post_meta( $id, '_amt_news_keywords' ) != null ) {
			$keywords .= ', ' . stripslashes( get_post_meta( $id, '_amt_news_keywords', true ) );
		}

		// Encode, lowercase & trim appropriately
		$keywords = self::normalize_keywords( $keywords );

		// Unique keywords
		$keywords_array = array();
		$keywords_array = explode( ', ', $keywords );
		$keywords_array = array_unique( $keywords_array );

		if ( empty( $keywords_array[0] ) ) {
			return array();
		} else {
			return $keywords_array;
		}
	}

	/**
	 * Normalizes and cleans up a list of comma separated keywords ie. encode, lowercase & trim appropriately
	 *
	 * @param string $keywords
	 * @return string
	 */
	public static function normalize_keywords( $keywords ) {
		return trim( trim( strtolower( trim( htmlspecialchars( htmlspecialchars_decode( $keywords ), ENT_QUOTES ) ) ), ',' ) );
	}

	/**
	 * Function to return a thumbnail for a given permalink
	 *
	 * @return thumbnail URL
	 */
	public static function permalink_thumbnail( $post_id = null, $size = 'shareaholic-thumbnail' ) {
		$thumbnail_src = '';

		// Get Featured Image
		$thumbnail_src = self::post_featured_image( $size );

		// Get first image included in the post
		if ( $thumbnail_src == null ) {
			$thumbnail_src = self::post_first_image( $post_id );
		}

		if ( $thumbnail_src == null ) {
			return null;
		} else {
			return $thumbnail_src;
		}
	}

	/**
	 * This function returns the URL of the featured image for a given post
	 *
	 * @return returns `false` or a string of the image src
	 */
	public static function post_featured_image( $size = 'shareaholic-thumbnail' ) {
		global $post;
		$featured_img = '';
		if ( $post == null ) {
			return false;
		} else {
			if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( $post->ID ) ) {
				// note: wp_get_attachment_image_src returns either an array or NULL
				$thumbnail_shareaholic = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'shareaholic-thumbnail' );
				$thumbnail_full        = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' ); // Original image resolution (unmodified)
				
				if ( ( $size == 'shareaholic-thumbnail' ) && ( ! empty( $thumbnail_shareaholic[0]) && ! empty( $thumbnail_full[0]) ) && ( $thumbnail_shareaholic[0] !== $thumbnail_full[0] ) ) {
					$featured_img = esc_attr( $thumbnail_shareaholic[0] );
				} else {
					if ( $size == 'shareaholic-thumbnail' ) {
						  $thumbnail_large = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' ); // Large resolution (default 1024px x 1024px max)
					} else {
						  $thumbnail_large = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $size );
					}
					if ( is_array( $thumbnail_large ) ) {
						$featured_img = esc_attr( $thumbnail_large[0] );
					}
				}
			} else {
				return false;
			}
		}
		return $featured_img;
	}

	/**
	 * Return Facebook Access Token
	 */
	public static function fetch_fb_access_token() {
		if ( self::get_option( 'facebook_app_id' ) && self::get_option( 'facebook_app_secret' ) ) {
			return self::get_option( 'facebook_app_id' ) . '%7C' . self::get_option( 'facebook_app_secret' );
		}
		return false;
	}


	/**
	 * This function grabs the URL of the first image in a given post
	 *
	 * @return returns `false` or a string of the image src
	 */
	public static function post_first_image() {
		global $post;
		$first_img = '';
		if ( $post == null ) {
			return false;
		} else {
			$output = preg_match_all( '/<img.*?src=[\'"](.*?)[\'"].*?>/i', $post->post_content, $matches );
			if ( isset( $matches[1][0] ) ) {
				// Exclude base64 images; meta tags require full URLs
				if ( strpos( $matches[1][0], 'data:' ) === false ) {
					$first_img = $matches[1][0];
				}
			} else {
				return false;
			}
			return $first_img;
		}
	}

	/**
	 * WP Rocket Compatability - Excludes Shareaholic scripts from JS minification, etc
	 *
	 * @param Array $excluded_external An array of JS hosts
	 * @return Array the updated array of hosts
	 */
	function rocket_exclude_js( $excluded_external ) {
		if ( defined( 'WP_ROCKET_VERSION' ) ) {
			$excluded_external[] = 'cdn.shareaholic.net';
			$excluded_external[] = 'k4z6w9b5.stackpathcdn.com';

			return $excluded_external;
		}
	}

	/*
	* Clears cache created by caching plugins like W3 Total Cache
	*
	*/
	public static function clear_cache() {

		// Default WordPress?
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
		// W3 Total Cache plugin?
		if ( function_exists( 'w3tc_pgcache_flush' ) ) {
			w3tc_pgcache_flush();
		}
		// WP Super Cache?
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			if ( is_multisite() ) {
				$blog_id = get_current_blog_id();
				wp_cache_clear_cache( $blog_id );
			} else {
				wp_cache_clear_cache();
			}
		}
		// Hyper Cache?
		if ( function_exists( 'hyper_cache_flush_all' ) ) {
			hyper_cache_flush_all();
		}
		// WP Fastest Cache
		if ( class_exists( 'WpFastestCache' ) ) {
			$WpFastestCache = new WpFastestCache();
			if ( method_exists( $WpFastestCache, 'deleteCache' ) ) {
				$WpFastestCache->deleteCache();
			}
		}
		// WPEngine?
		if ( class_exists( 'WpeCommon' ) ) {
			if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
				WpeCommon::purge_memcached();
			}
			if ( method_exists( 'WpeCommon', 'clear_maxcdn_cache' ) ) {
				WpeCommon::clear_maxcdn_cache();
			}
			if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
				WpeCommon::purge_varnish_cache();
			}
		}
		// Cachify Cache?
		if ( has_action( 'cachify_flush_cache' ) ) {
			do_action( 'cachify_flush_cache' );
		}
		// Quick Cache?
		if ( function_exists( 'auto_clear_cache' ) ) {
			auto_clear_cache();
		}
		// Zencache?
		if ( class_exists( 'zencache' ) ) {
			zencache::clear();
		}
		// CometCache?
		if ( class_exists( 'comet_cache' ) ) {
			comet_cache::clear();
		}
	}

	/**
	 * List below is from Jetpack, with a few custom additions:
	 * Source: https://github.com/Automattic/jetpack/blob/master/sync/class.jetpack-sync-defaults.php
	 **/
	static $blacklisted_post_types = array(
		'nav_menu_item',
		'attachment',
		'ai1ec_event',
		'bwg_album',
		'bwg_gallery',
		'customize_changeset', // WP built-in post type for Customizer changesets
		'dn_wp_yt_log',
		'http',
		'idx_page',
		'jetpack_migration',
		'postman_sent_mail',
		'rssap-feed',
		'rssmi_feed_item',
		'secupress_log_action',
		'sg_optimizer_jobs',
		'snitch',
		'wpephpcompat_jobs',
		'wprss_feed_item',
		'wp_automatic',
		'jp_sitemap_master',
		'jp_sitemap',
		'jp_sitemap_index',
		'jp_img_sitemap',
		'jp_img_sitemap_index',
		'jp_vid_sitemap',
		'jp_vid_sitemap_index',
	);

	/**
	 * A post just transitioned state. Do something.
	 */
	public static function post_transitioned( $new_status, $old_status, $post ) {
		$post_type = get_post_type( $post );

		// exit if blacklisted post type
		if ( $post_type && in_array( $post_type, self::$blacklisted_post_types ) ) {
			return;
		}

		if ( $new_status == 'publish' ) {
			// Post was just published
			self::notify_content_manager_singlepage( $post );
		}
		if ( $old_status == 'publish' && $new_status != 'publish' ) {
			// Notify CM that the post is no longer public
			self::notify_content_manager_singlepage( $post );
		}
	}

	/**
	 * Server Connectivity check
	 */
	public static function connectivity_check() {
		$health_check_url = Shareaholic::API_URL . '/haproxy_health_check';
		$response         = ShareaholicCurl::get( $health_check_url );
		if ( is_array( $response ) && array_key_exists( 'body', $response ) ) {
			$response_code = wp_remote_retrieve_response_code( $response );
			if ( $response_code == '200' ) {
				return 'SUCCESS';
			} else {
				return 'FAIL';
			}
		} else {
			return 'FAIL';
		}
	}

	/**
	 * Site ID 200 check
	 */
	public static function site_id_is200() {
		$api_key = self::get_option( 'api_key' );
		if ( $api_key ) {
			if (strlen( $api_key ) < 30 ) {
				return 'invalid_apikey';
			}
			$response = ShareaholicCurl::get( Shareaholic::API_URL . "/publisher_tools/{$api_key}.json" );
			if ( is_array( $response ) && array_key_exists( 'body', $response ) ) {
				$response_code = wp_remote_retrieve_response_code( $response );
				if ( $response_code == '200' ) {
					return '200';
				} else {
					return 'not_200';
				}
			} else {
				return 'no_connection';
			}
		} else {
			return 'no_apikey';
		}
	}
	
	/**
	 * Site ID 404 check
	 */
	public static function site_id_is404() {
		$api_key = self::get_option( 'api_key' );
		if ( $api_key ) {
			if (strlen( $api_key ) < 30 ) {
				return 'invalid_apikey';
			}
			$response = ShareaholicCurl::get( Shareaholic::API_URL . "/publisher_tools/{$api_key}.json" );
			if ( is_array( $response ) && array_key_exists( 'body', $response ) ) {
				$response_code = wp_remote_retrieve_response_code( $response );
				if ( $response_code == '404' ) {
					return '404';
				} else {
					return 'not_404';
				}
			} else {
				return 'no_connection';
			}
		} else {
			return 'no_apikey';
		}
	}

	/**
	 * Manages V8 => V9 upgrade
	 */
	public static function EightToNineUpdate() {
		$api_key = self::get_option( 'api_key' );
		if ( $api_key ) {
			$response = ShareaholicCurl::get( Shareaholic::API_URL . "/publisher_tools/{$api_key}.json" );

			if ( is_array( $response ) && array_key_exists( 'body', $response ) ) {
				$response_code = wp_remote_retrieve_response_code( $response );
				if ( $response_code == '404' ) {
					// Site ID is not active in Shareaholic Cloud.
					return;
				}
			}

			// If Cloud location = off then set Plugin location = off.
			$publisher_configuration = $response['body'];
			if ( $publisher_configuration && is_array( $publisher_configuration ) ) {
				foreach ( array( 'share_buttons', 'recommendations' ) as $app ) {
					$cloud_result = array();
					foreach ( $publisher_configuration['apps'][ $app ]['locations'] as $key => $val ) {
						if ( array_key_exists( $val['name'], self::get_option( $app ) ) ) {
							// Update only if location already exists in Plugin settings
							if ( $val['enabled'] == false ) {
								self::update_options( array( $app => array( $val['name'] => 'off' ) ) );
							}
						}
					}
				}
			}

			// Send Plugin settings to Cloud.
			self::location_sync_to_cloud();
		}
	}

	/**
	 * Prepare associative array of locations, and their parameters.
	 */
	public static function prepareLocationsArray( $locations ) {
		$formatted_app_locations = array();
		foreach ( $locations as $key => $val ) {
			if ( $val == 'on' ) {
				$formatted_app_locations[ $key ] = array( 'enabled' => true );
			} else {
				$formatted_app_locations[ $key ] = array( 'enabled' => false );
			}
		}
		if ( empty( $formatted_app_locations ) ) {
			// By casting the array into an object, json_encode will always use braces instead of brackets for the value (even when empty)
			return (object) array();
		} else {
			return $formatted_app_locations;
		}
	}

	/**
	 *  Sync In-Page Location Blocks - Shareaholic Cloud => WP Plugin
	 */
	public static function location_sync_update_plugin() {
		$api_key = self::get_option( 'api_key' );
		if ( $api_key ) {
			$response = ShareaholicCurl::get( Shareaholic::API_URL . "/publisher_tools/{$api_key}.json" );

			if ( is_array( $response ) && array_key_exists( 'body', $response ) ) {
				$response_code = wp_remote_retrieve_response_code( $response );
				if ( $response_code == '404' ) {
					// Site ID is not active in Shareaholic Cloud
					return;
				}
			}

			$publisher_configuration = $response['body'];
			if ( $publisher_configuration && is_array( $publisher_configuration ) ) {
				foreach ( array( 'share_buttons', 'recommendations' ) as $app ) {
					$cloud_result = array();
					foreach ( $publisher_configuration['apps'][ $app ]['locations'] as $key => $val ) {
						if ( array_key_exists( $val['name'], self::get_option( $app ) ) ) {
							// Update only if location already exists in Plugin settings
							if ( $val['enabled'] == true ) {
								self::update_options( array( $app => array( $val['name'] => 'on' ) ) );
							} else {
								self::update_options( array( $app => array( $val['name'] => 'off' ) ) );
							}
						}
					}
				}
			}
		}
	}

	/**
	 *  Sync status of In-Page Location Blocks - WP Plugin <=> Shareaholic Cloud
	 */
	public static function location_sync_status() {
		$api_key = self::get_option( 'api_key' );
		if ( $api_key ) {
			$app_locations = array(
				'share_buttons'   => self::prepareLocationsArray( self::get_option( 'share_buttons' ) ),
				'recommendations' => self::prepareLocationsArray( self::get_option( 'recommendations' ) ),
			);

			$payload = array(
				'verification_key' => self::get_option( 'verification_key' ),
				'app_locations'    => $app_locations,
			);

			$sync_status_url = Shareaholic::API_URL . '/publisher_tools/' . $api_key . '/sync/status';

			$response = ShareaholicCurl::post(
				$sync_status_url,
				$payload,
				'json',
				true
			);

			if ( is_array( $response ) && array_key_exists( 'body', $response ) ) {
				$response_code = wp_remote_retrieve_response_code( $response );
				if ( $response_code == '200' ) {
					return 200;
				}
				if ( $response_code == '409' ) {
					return 409;
				}
				if ( $response_code == '404' ) {
					return 404;
				}
			} else {
				return null;
			}
		}
	}

	/**
	 *  Sync In-Page Location Blocks - WP Plugin => Shareaholic Cloud
	 */
	public static function location_sync_to_cloud() {
		$api_key = self::get_option( 'api_key' );
		if ( $api_key ) {
			$app_locations = array(
				'share_buttons'   => self::prepareLocationsArray( self::get_option( 'share_buttons' ) ),
				'recommendations' => self::prepareLocationsArray( self::get_option( 'recommendations' ) ),
			);

			$payload = array(
				'verification_key' => self::get_option( 'verification_key' ),
				'app_locations'    => $app_locations,
			);

			$sync_url = Shareaholic::API_URL . '/publisher_tools/' . $api_key . '/sync';

			$response = ShareaholicCurl::post(
				$sync_url,
				$payload,
				'json',
				true
			);

			if ( is_array( $response ) && array_key_exists( 'body', $response ) ) {
				$response_code = wp_remote_retrieve_response_code( $response );
				if ( $response_code == '200' ) {
					return true;
				}
			} else {
				return null;
			}
		}
	}

	/**
	 * Facebook Auth Token check
	 */
	public static function facebook_auth_check() {
		if ( self::fetch_fb_access_token() === false ) {
			self::update_options( array( 'facebook_auth_check' => 'FAIL' ) );
			return 'FAIL';
		}

		$health_check_url = 'https://graph.facebook.com/?id=https://www.google.com/&access_token=' . self::fetch_fb_access_token();

		$response = ShareaholicCurl::get( $health_check_url );

		if ( is_array( $response ) && array_key_exists( 'body', $response ) ) {
			$response_code = wp_remote_retrieve_response_code( $response );
			if ( $response_code == '200' ) {
				self::update_options( array( 'facebook_auth_check' => 'SUCCESS' ) );
				return 'SUCCESS';
			} else {
				self::update_options( array( 'facebook_auth_check' => 'FAIL' ) );
				return 'FAIL';
			}
		} else {
			return 'FAIL';
		}
	}

	/**
	 * Share Counts API Connectivity check
	 */
	public static function share_counts_api_connectivity_check() {

		// if we already checked and it is successful, then do not call the API again
		$share_counts_connect_check = self::get_option( 'share_counts_connect_check' );
		if ( isset( $share_counts_connect_check ) && $share_counts_connect_check == 'SUCCESS' ) {
			return $share_counts_connect_check;
		}

		$services_config      = ShareaholicSeqShareCount::get_services_config();
		$services             = array_keys( $services_config );
		$param_string         = implode( '&services[]=', $services );
		$share_counts_api_url = admin_url( 'admin-ajax.php' ) . '?action=shareaholic_share_counts_api&url=https%3A%2F%2Fwww.google.com%2F&services[]=' . $param_string;
		$cache_key            = 'share_counts_api_connectivity_check';

		$response = get_transient( $cache_key );
		if ( ! $response ) {
			$response = ShareaholicCurl::get( $share_counts_api_url, array(), '', true );
		}

		$response_status = self::get_share_counts_api_status( $response );
		// if this was the first time we are doing this and it failed, disable
		// the share counts API
		if ( empty( $share_counts_connect_check ) && $response_status == 'FAIL' ) {
			self::update_options( array( 'disable_internal_share_counts_api' => 'on' ) );
		}

		if ( $response_status == 'SUCCESS' ) {
			set_transient( $cache_key, $response, SHARE_COUNTS_CHECK_CACHE_LENGTH );
		}

		self::update_options( array( 'share_counts_connect_check' => $response_status ) );
		return $response_status;
	}

	/**
	 * Check the share counts API for empty response or missing services
	 */
	public static function get_share_counts_api_status( $response ) {
		if ( ! $response || ! isset( $response['body'] ) || ! is_array( $response['body'] ) || ! isset( $response['body']['data'] ) ) {
			return 'FAIL';
		}

		// Did it return at least 4 services?
		$has_majority_services  = count( array_keys( $response['body']['data'] ) ) >= 4 ? true : false;
		$has_important_services = true;
		// Does it have counts for facebook, pinterest?
		foreach ( array( 'facebook', 'pinterest' ) as $service ) {
			if ( ! isset( $response['body']['data'][ $service ] ) || ! is_numeric( $response['body']['data'][ $service ] ) ) {
				$has_important_services = false;
			}
		}

		if ( ! $has_majority_services || ! $has_important_services ) {
			return 'FAIL';
		}

		return 'SUCCESS';
	}


	/**
	 * Call the content manager for a post before it is trashed
	 *
	 * We do this because permalink changes on being trashed
	 * and so we tell CM that the old permalink is no longer valid
	 */
	public static function before_post_is_trashed( $post_id ) {

		$post_type = get_post_type( $post_id );

		// exit if blacklisted post type
		if ( $post_type && in_array( $post_type, self::$blacklisted_post_types ) ) {
			return;
		}

		self::notify_content_manager_singlepage( get_post( $post_id ), 'trash' );
	}


	/**
	 * Call the content manager for a post before it is updated
	 *
	 * We do this because a user may change their permalink
	 * and so we tell CM that the old permalink is no longer valid
	 */
	public static function before_post_is_updated( $post_id ) {

		$post_type = get_post_type( $post_id );

		// exit if blacklisted post type
		if ( $post_type && in_array( $post_type, self::$blacklisted_post_types ) ) {
			return;
		}

		self::notify_content_manager_singlepage( get_post( $post_id ) );
	}

	public static function user_info() {
		$user_info = array();

		if ( function_exists( 'wp_get_current_user' ) ) {

			$current_user = wp_get_current_user();

			if ( ! ( $current_user instanceof WP_User ) || ! is_user_logged_in() ) {
				return array();
			}

			$user_caps = $current_user->get_role_caps();

			$caps = array(
				'switch_themes',
				'edit_themes',
				'activate_plugins',
				'edit_plugins',
				'manage_options',
				'unfiltered_html',
				'edit_dashboard',
				'update_plugins',
				'delete_plugins',
				'install_plugins',
				'update_themes',
				'install_themes',
				'update_core',
				'edit_theme_options',
				'delete_themes',
				'administrator',
			);

			$user_info = array(
				'username'       => $current_user->user_login,
				'email'          => $current_user->user_email,
				'roles'          => $current_user->roles,
				'capabilities'   => array(),
				'is_super_admin' => is_super_admin(),
			);

			foreach ( $caps as $cap ) {
				$user_info['capabilities'][ $cap ] = isset( $user_caps[ $cap ] ) ? $user_caps[ $cap ] : '';
			}
		}

		return $user_info;
	}

	/**
	 * Shorten a string to a certain character limit
	 * If the limit is reached, then return the truncated text
	 *
	 * @param {String} $text the text to truncate.
	 * @param {Number} $char_count the max number of characters.
	 * @return {String} the truncated text
	 */
	public static function truncate_text( $text, $char_count ) {
		$words          = preg_split( '/\s+/', $text );
		$truncated_text = '';

		foreach ( $words as $word ) {
			if ( strlen( $word ) + strlen( $truncated_text ) >= $char_count ) {
				break;
			}

			$truncated_text .= ' ' . $word;
		}

		return trim( $truncated_text );
	}
}
