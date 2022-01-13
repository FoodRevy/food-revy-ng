<?php
/*
Plugin Name: Shareaholic - Grow and Engage Your Audience
Plugin URI: https://www.shareaholic.com/website-tools/
Description: Shareaholic’s official WordPress plugin allows you to add Award-Winning Social Share Buttons, Related Posts, Content Analytics, Ad Monetization, and more to your website.
Version: 9.7.2
Author: Shareaholic
Author URI: https://www.shareaholic.com
Text Domain: shareaholic
Domain Path: /languages
*/

/**
 * The main file!
 *
 * @package shareaholic
 * @version 9.7.2
 */

/**
 * Make sure we don't expose any info if called directly
 */
if ( ! function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/**
* if we ever wanted to disable warning notices, use the following:
* error_reporting(E_ALL ^ E_NOTICE);
*/

if ( ! defined( 'SHAREAHOLIC_FILE' ) ) {
	define( 'SHAREAHOLIC_FILE', __FILE__ );
}

if ( ! defined( 'SHAREAHOLIC_PATH' ) ) {
	define( 'SHAREAHOLIC_PATH', plugin_dir_path( SHAREAHOLIC_FILE ) );
}

// Caching
if ( ! defined( 'SHARE_COUNTS_CHECK_CACHE_LENGTH' ) ) {
	define( 'SHARE_COUNTS_CHECK_CACHE_LENGTH', 900 ); // 900 seconds
}

// because define can use function returns and const can't
if ( ! defined( 'SHAREAHOLIC_DEBUG' ) ) {
	define( 'SHAREAHOLIC_DEBUG', getenv( 'SHAREAHOLIC_DEBUG' ) );
}

require_once SHAREAHOLIC_PATH . 'utilities.php';
require_once SHAREAHOLIC_PATH . 'global_functions.php';
require_once SHAREAHOLIC_PATH . 'admin.php';
require_once SHAREAHOLIC_PATH . 'public.php';
require_once SHAREAHOLIC_PATH . 'notifier.php';
require_once SHAREAHOLIC_PATH . 'cron.php';

if ( ! class_exists( 'Shareaholic' ) ) {
	/**
	 * The main / base class.
	 *
	 * @package shareaholic
	 */
	class Shareaholic {

		const URL        = 'https://www.shareaholic.com';
		const API_URL    = 'https://web.shareaholic.com'; // uses static IPs for firewall whitelisting
		const CM_API_URL = 'https://cm-web.shareaholic.com'; // uses static IPs for firewall whitelisting

		const VERSION = '9.7.2';

		/**
		 * Starts off as false so that ::get_instance() returns
		 * a new instance.
		 */
		private static $instance = false;

		/**
		 * The constructor registers all the WordPress actions.
		 */
		private function __construct() {
			add_action( 'wp_ajax_shareaholic_accept_terms_of_service', array( 'ShareaholicUtilities', 'accept_terms_of_service' ) );

			// Share Counts API
			add_action( 'wp_ajax_nopriv_shareaholic_share_counts_api', array( 'ShareaholicPublic', 'share_counts_api' ) );
			add_action( 'wp_ajax_shareaholic_share_counts_api', array( 'ShareaholicPublic', 'share_counts_api' ) );

			// Debug info
			add_action( 'wp_ajax_nopriv_shareaholic_debug_info', array( 'ShareaholicPublic', 'debug_info' ) );
			add_action( 'wp_ajax_shareaholic_debug_info', array( 'ShareaholicPublic', 'debug_info' ) );

			// Permalink list for Related Content index
			add_action( 'wp_ajax_nopriv_shareaholic_permalink_list', array( 'ShareaholicPublic', 'permalink_list' ) );
			add_action( 'wp_ajax_shareaholic_permalink_list', array( 'ShareaholicPublic', 'permalink_list' ) );

			// SDK Badge
			add_action( 'wp_ajax_nopriv_shareaholic_sdk_info', array( 'ShareaholicPublic', 'sdk_info' ) );
			add_action( 'wp_ajax_shareaholic_sdk_info', array( 'ShareaholicPublic', 'sdk_info' ) );

			// Permalink info for Related Content index
			add_action( 'wp_ajax_nopriv_shareaholic_permalink_info', array( 'ShareaholicPublic', 'permalink_info' ) );
			add_action( 'wp_ajax_shareaholic_permalink_info', array( 'ShareaholicPublic', 'permalink_info' ) );

			// Related Permalinks for Related Content app bootup
			add_action( 'wp_ajax_nopriv_shareaholic_permalink_related', array( 'ShareaholicPublic', 'permalink_related' ) );
			add_action( 'wp_ajax_shareaholic_permalink_related', array( 'ShareaholicPublic', 'permalink_related' ) );

			add_action( 'after_setup_theme', array( 'ShareaholicPublic', 'after_setup_theme' ) );

			add_action( 'pre_get_posts', array( $this, 'shareaholic_draw_canvas' ) );

			add_action( 'wp_head', array( 'ShareaholicPublic', 'wp_head' ), 6 );
			add_filter( 'wp_resource_hints', array( 'ShareaholicPublic', 'shareaholic_resource_hints' ), 10, 2 );
			add_shortcode( 'shareaholic', array( 'ShareaholicPublic', 'shortcode' ) );

			add_action( 'plugins_loaded', array( $this, 'shareaholic_init' ) );

			add_action( 'admin_init', array( 'ShareaholicAdmin', 'admin_init' ) );
			add_action( 'admin_enqueue_scripts', array( 'ShareaholicAdmin', 'admin_header' ) );
			add_action( 'wp_ajax_shareaholic_add_location', array( 'ShareaholicAdmin', 'add_location' ) );
			add_action( 'add_meta_boxes', array( 'ShareaholicAdmin', 'add_meta_boxes' ) );
			add_action( 'save_post', array( 'ShareaholicAdmin', 'save_post' ) );
			add_action( 'admin_enqueue_scripts', array( 'ShareaholicAdmin', 'enqueue_scripts' ) );
			add_action( 'admin_menu', array( 'ShareaholicAdmin', 'admin_menu' ) );

			// add_action('publish_post', array('ShareaholicNotifier', 'post_notify'));
			// add_action('publish_page', array('ShareaholicNotifier', 'post_notify'));

			add_action( 'transition_post_status', array( 'ShareaholicUtilities', 'post_transitioned' ), 10, 3 );

			register_activation_hook( __FILE__, array( $this, 'after_activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
			register_uninstall_hook( __FILE__, array( 'Shareaholic', 'uninstall' ) );

			add_action( 'wp_before_admin_bar_render', array( 'ShareaholicUtilities', 'admin_bar_extended' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ShareaholicUtilities::admin_plugin_action_links', -10 );

			// Add custom action to run remove_transients cron job
			add_action( 'shareaholic_remove_transients_hourly', array( 'ShareaholicCron', 'remove_transients' ) );

			// Add custom action to run shareaholic_heartbeat cron job
			add_action( 'shareaholic_heartbeat', array( 'ShareaholicCron', 'heartbeat' ) );

			// do something before a post is updated
			add_action( 'pre_post_update', array( 'ShareaholicUtilities', 'before_post_is_updated' ) );

			// do something before a post is trashed
			add_action( 'pre_trash_post', array( 'ShareaholicUtilities', 'before_post_is_trashed' ) );

			// do something before a site's permalink structure changes
			add_action( 'update_option_permalink_structure', array( 'ShareaholicUtilities', 'notify_content_manager_singledomain' ) );

			// Show ToS notice
			if ( ! ShareaholicUtilities::has_accepted_terms_of_service() ) {
				add_action( 'admin_notices', array( 'ShareaholicAdmin', 'show_terms_of_service' ) );
			}

			// use the admin notice API
			add_action( 'admin_notices', array( 'ShareaholicAdmin', 'admin_notices' ) );

			// Add our custom image type to the Media Library
			add_filter( 'image_size_names_choose', array( 'ShareaholicAdmin', 'show_custom_sizes' ) );

			// ShortCode UI plugin specific hooks to prevent duplicate app rendering
			// https://wordpress.org/support/topic/custom-post-type-exclude-issue?replies=10#post-3370550
			add_action( 'scui_external_hooks_remove', array( $this, 'remove_apps' ) );
			add_action( 'scui_external_hooks_return', array( $this, 'return_apps' ) );

			// WP Rocket Compatability
			add_filter( 'rocket_minify_excluded_external_js', array( 'ShareaholicUtilities', 'rocket_exclude_js' ) );
		}

		public static function shareaholic_draw_canvas( $query ) {
			if ( $query->is_main_query() ) {
				// Hook to change the priority number of In-Page Block canvases
				// Example: add_filter( 'shareaholic_content_priority', 20 );
				$priority = apply_filters( 'shareaholic_content_priority', 98 );

				add_filter( 'the_content', array( 'ShareaholicPublic', 'draw_canvases' ), $priority );
				add_filter( 'the_excerpt', array( 'ShareaholicPublic', 'draw_canvases' ), $priority );
			}
		}

		public static function remove_apps() {
			remove_filter( 'the_content', array( 'ShareaholicPublic', 'draw_canvases' ), 98 );
			remove_filter( 'the_excerpt', array( 'ShareaholicPublic', 'draw_canvases' ), 98 );
		}

		public static function return_apps() {
			add_filter( 'the_content', array( 'ShareaholicPublic', 'draw_canvases' ), 98 );
			add_filter( 'the_excerpt', array( 'ShareaholicPublic', 'draw_canvases' ), 98 );
		}

		/**
		 * We want this to be a singleton, so return the one instance
		 * if already instantiated.
		 *
		 * @return Shareaholic
		 */
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}
			self::init();
			return self::$instance;
		}

		/**
		 * This function initializes the plugin so that everything is scoped
		 * under the class and no variables leak outside.
		 */
		public static function init() {
			self::update();
		}

		/**
		 * This function fires once any activated plugins have been loaded. Is generally used for immediate filter setup, or plugin overrides.
		 */
		public function shareaholic_init() {
			ShareaholicUtilities::localize();

			if ( ShareaholicUtilities::has_accepted_terms_of_service() &&
			isset( $_GET['page'] ) && preg_match( '/shareaholic/', $_GET['page'] ) ) {
				ShareaholicUtilities::get_or_create_api_key();
			}
		}

		/**
		 * Runs update code if the plugin version is different from what is stored in the settings.
		 */
		public static function update() {
			if ( ShareaholicUtilities::get_version() != self::VERSION ) {
				ShareaholicUtilities::perform_update();
				ShareaholicUtilities::set_version( self::VERSION );
				ShareaholicUtilities::log_event( 'Upgrade', array( 'previous_plugin_version' => ShareaholicUtilities::get_version() ) );
			}
		}

		/**
		 * Checks whether to ask the user to accept the terms of service or not.
		 */
		public function terms_of_service() {
			if ( ! ShareaholicUtilities::has_accepted_terms_of_service() ) {
				add_action( 'admin_notices', array( 'ShareaholicAdmin', 'show_terms_of_service' ) );
			} else {
				ShareaholicUtilities::get_or_create_api_key();
			}
		}

		/**
		 * This function fires after the plugin has been activated.
		 */
		public function after_activation() {
			set_transient( '_shr_activation_redirect', true, 120 ); // for post-activation redirect

			// Cleanup leftover mutex
			ShareaholicUtilities::delete_mutex();

			$this->terms_of_service();
			ShareaholicUtilities::log_event( 'Activate' );

			// workaround: http://codex.wordpress.org/Function_Reference/register_activation_hook
			add_option( 'Activated_Plugin_Shareaholic', 'shareaholic' );
			$api_key = ShareaholicUtilities::get_option( 'api_key' );
			if ( ShareaholicUtilities::has_accepted_terms_of_service() && ! empty( $api_key ) ) {
				ShareaholicUtilities::notify_content_manager_singledomain();
			}

			if ( ! ShareaholicUtilities::get_version() ) {
				ShareaholicUtilities::log_event( 'Install_Fresh' );
			}

			// Activate the Shareaholic Cron Job for new users
			ShareaholicCron::activate();
		}

		/**
		 * This function fires when the plugin is deactivated.
		 */
		public function deactivate() {
			ShareaholicUtilities::log_event( 'Deactivate' );
			ShareaholicUtilities::clear_cache();
			ShareaholicCron::deactivate();
			ShareaholicUtilities::delete_mutex();
		}

		/**
		 * This function fires when the plugin is uninstalled.
		 */
		public static function uninstall() {
			ShareaholicUtilities::log_event( 'Uninstall' );
			delete_option( 'shareaholic_has_accepted_tos' );
			delete_option( 'shareaholic_settings' );
			delete_option( 'shareaholic_activate_timestamp' );
			delete_option( 'shareaholic_review_dismiss' ); // legacy flag for reviews; can remove later
			ShareaholicUtilities::delete_api_key();
			ShareaholicUtilities::delete_mutex();
		}
	}

	// the magic
	$shareaholic = Shareaholic::get_instance();

} else {
	/* PLUGIN SPECIFIC CODE STARTS HERE */
	register_activation_hook( __FILE__, 'shareaholic_update_primary_plugin' );
}

function shareaholic_update_primary_plugin() {
	if ( is_plugin_active( 'sexybookmarks/shareaholic.php' ) ) {
		add_action( 'update_option_active_plugins', 'shareaholic_activate_primary_plugin' );
	}
}

function shareaholic_activate_primary_plugin() {
	deactivate_plugins( 'sexybookmarks/shareaholic.php' );
	activate_plugins( 'shareaholic/shareaholic.php' );
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ShareaholicUtilities::admin_plugin_action_links', -10 );
}
/* PLUGIN SPECIFIC CODE ENDS HERE */
