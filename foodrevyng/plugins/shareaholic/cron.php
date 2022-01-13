<?php
/**
 * Holds the ShareaholicCron class.
 *
 * @package shareaholic
 */

/**
 * This class will contain all the cron jobs executed by this plugin
 *
 * @package shareaholic
 */
class ShareaholicCron {

	/**
	 * Schedules the cron jobs if it does not exist
	 */
	public static function activate() {

		// Transient Cleanup
		if ( ! wp_next_scheduled( 'shareaholic_remove_transients_hourly' ) ) {
			// schedule the first occurrence 1 min from now
			wp_schedule_event(
				time() + 60,
				'hourly',
				'shareaholic_remove_transients_hourly'
			);
			ShareaholicUtilities::log( 'Cron: shareaholic_remove_transients_hourly is now scheduled' );
		} else {
			ShareaholicUtilities::log( 'Cron: shareaholic_remove_transients_hourly is already scheduled' );
		}

		// Content Sync Heartbeat
		if ( ! wp_next_scheduled( 'shareaholic_heartbeat' ) ) {
			wp_schedule_event(
				time() + rand( 1, 60 ),
				'daily',
				'shareaholic_heartbeat'
			);
			ShareaholicUtilities::log( 'Cron: shareaholic_heartbeat is now scheduled' );
		} else {
			ShareaholicUtilities::log( 'Cron: shareaholic_heartbeat is already scheduled' );
		}
	}

	/**
	 * Remove scheduled cron jobs created by Shareaholic
	 */
	public static function deactivate() {
		if ( wp_next_scheduled( 'shareaholic_remove_transients_hourly' ) ) {
			wp_clear_scheduled_hook( 'shareaholic_remove_transients_hourly' );
			ShareaholicUtilities::log( 'Cron: shareaholic_remove_transients_hourly cleared' );
		} else {
			ShareaholicUtilities::log( 'Cron: no need to clear shareaholic_remove_transients_hourly schedule' );
		}

		if ( wp_next_scheduled( 'shareaholic_heartbeat' ) ) {
			wp_clear_scheduled_hook( 'shareaholic_heartbeat' );
			ShareaholicUtilities::log( 'Cron: shareaholic_heartbeat cleared' );
		} else {
			ShareaholicUtilities::log( 'Cron: no need to clear shareaholic_heartbeat schedule' );
		}
	}

	public static function heartbeat() {
		if ( ShareaholicUtilities::has_accepted_terms_of_service() ) {
			ShareaholicUtilities::heartbeat();
		}
	}

	/**
	 * A job that clears up the shareaholic share counts transients
	 */
	public static function remove_transients() {
		global $wpdb;
		$older_than = time() - ( 60 * 60 ); // older than an hour ago

		ShareaholicUtilities::log( 'Start of Shareaholic transient cleanup' );

		$query      = "SELECT REPLACE(option_name, '_transient_timeout_', '') AS transient_name FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\_shr\_api\_res-%%' AND option_value < %s LIMIT 5000";
		$transients = $wpdb->get_col( $wpdb->prepare( $query, $older_than ) );

		$options_names = array();
		foreach ( $transients as $transient ) {
			$options_names[] = esc_sql( '_transient_' . $transient );
			$options_names[] = esc_sql( '_transient_timeout_' . $transient );
		}
		if ( $options_names ) {
			$options_names = "'" . implode( "','", $options_names ) . "'";
			$result        = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name IN ({$options_names})" );

			if ( ! $result ) {
				ShareaholicUtilities::log( 'Transient Query Error!' );
			}
		}
		// Cleanup leftover mutex
		ShareaholicUtilities::delete_mutex();

		ShareaholicUtilities::log( 'End of Shareaholic transient cleanup' );
	}
}
