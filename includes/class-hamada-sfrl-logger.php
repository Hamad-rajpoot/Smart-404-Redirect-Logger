<?php
/**
 * Handles logging of 404 page requests into the database.
 *
 * @package Hamada_Smart_404_Redirect_Logger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SFRL_Logger {

	/**
	 * Constructor - hook into WordPress.
	 */
	public function __construct() {

		// Detect 404 and log it
		add_action( 'template_redirect', array( $this, 'detect_404' ) );

		// Listen for daily cleanup cron
		add_action( 'sfrl_cleanup_old_logs', array( $this, 'delete_old_logs' ) );
	}

	/**
	 * Detect if current request is a 404 page.
	 */
	public function detect_404() {

		// Check if logging is enabled
		$enable_logging = get_option( 'hamada_sfrl_enable_logging', true );

		if ( ! $enable_logging ) {
			return;
		}

		// Skip admin, AJAX, or REST API requests
		if ( is_admin() || defined( 'DOING_AJAX' ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		if ( is_404() ) {
			$this->log_404_request();
		}
	}

	/**
	 * Log the 404 request into the database.
	 */
	private function log_404_request() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sfrl_404_logs';

		$missing_url  = esc_url_raw( $_SERVER['REQUEST_URI'] ?? '' );
		$referrer     = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '';
		$ip_address   = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );
		$current_time = current_time( 'mysql' );

		if ( empty( $missing_url ) ) {
			return;
		}

		// --- Exclude URLs (keyword-based match) ---
		$excluded_urls = get_option( 'sfrl_excluded_urls', '' );
		if ( ! empty( $excluded_urls ) ) {
			$excluded_list = array_map( 'trim', explode( ',', $excluded_urls ) );
			foreach ( $excluded_list as $excluded ) {
				if ( stripos( $missing_url, $excluded ) !== false ) {
					return; // Do not log excluded URLs
				}
			}
		}

		// --- Exclude IP Addresses ---
		$excluded_ips = get_option( 'sfrl_excluded_ips', '' );
		if ( ! empty( $excluded_ips ) ) {
			$ip_list = array_map( 'trim', explode( ',', $excluded_ips ) );
			if ( in_array( $ip_address, $ip_list, true ) ) {
				return; // Do not log excluded IPs
			}
		}

		// --- Avoid Duplicate Logs for the same URL ---
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM $table_name WHERE missing_url = %s LIMIT 1",
				$missing_url
			)
		);

		if ( empty( $existing ) ) {

    // First time → insert new row
    $wpdb->insert(
        $table_name,
        [
            'missing_url' => $missing_url,
            'referrer'    => $referrer,
            'ip_address'  => $ip_address,
            'date_logged' => $current_time,
        ],
        [ '%s', '%s', '%s', '%s' ]
    );

} else {

    // Already exists → update referrer if new referrer arrived
    if ( ! empty( $referrer ) ) {
        $wpdb->update(
            $table_name,
            [ 'referrer' => $referrer ],
            [ 'id'       => $existing ],
            [ '%s' ],
            [ '%d' ]
        );
    }

}

	}

	/**
	 * Delete logs older than X days (controlled from settings).
	 */
	public function delete_old_logs() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sfrl_404_logs';
		$days       = absint( get_option( 'sfrl_delete_after_days', 0 ) );

		if ( $days > 0 ) {
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM $table_name WHERE date_logged < DATE_SUB(NOW(), INTERVAL %d DAY)",
					$days
				)
			);
		}
	}
}
