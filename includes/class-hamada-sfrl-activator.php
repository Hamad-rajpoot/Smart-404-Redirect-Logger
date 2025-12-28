<?php
/**
 * Handles plugin activation tasks such as creating database tables.
 *
 * @package Hamada_Smart_404_Redirect_Logger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Hamada_SFRL_Activator {

	/**
	 * Run on plugin activation.
	 */
	public static function activate() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		// --- Table 1: 404 Logs ---
		$logs_table = $wpdb->prefix . 'sfrl_404_logs';
		$sql_logs = "CREATE TABLE IF NOT EXISTS $logs_table (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			missing_url TEXT NOT NULL,
			referrer TEXT NULL,
			redirected_to TEXT NULL,
			ip_address VARCHAR(100) NULL,
			date_logged DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;";
		dbDelta( $sql_logs );

		// --- Table 2: Manual Redirects ---
		$redirects_table = $wpdb->prefix . 'sfrl_manual_redirects';
		$sql_redirects = "CREATE TABLE IF NOT EXISTS $redirects_table (
			id MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
			from_url VARCHAR(255) NOT NULL,
			to_url VARCHAR(255) NOT NULL,
			date_added DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";
		dbDelta( $sql_redirects );

		// --- Save plugin version ---
		if ( ! defined( 'SFRL_VERSION' ) ) {
			define( 'SFRL_VERSION', '1.0.0' );
		}
		add_option( 'sfrl_version', SFRL_VERSION );

		// --- Schedule Daily Cleanup Cron Event ---
		if ( ! wp_next_scheduled( 'sfrl_cleanup_old_logs' ) ) {
			wp_schedule_event( time(), 'daily', 'sfrl_cleanup_old_logs' );
		}
	}
}
