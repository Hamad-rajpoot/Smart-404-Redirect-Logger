<?php
/**
 * Handles plugin activation tasks such as creating database tables.
 *
 * @package Smart_404_Redirect_Logger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SFRL_Activator {

	/**
	 * Run on plugin activation.
	 */
	public static function activate() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'sfrl_404_logs';
		$charset_collate = $wpdb->get_charset_collate();

		// SQL to create table if it doesn't exist
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			missing_url TEXT NOT NULL,
			referrer TEXT NULL,
			redirected_to TEXT NULL,
			ip_address VARCHAR(100) NULL,
			date_logged DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;";

		// Load dbDelta function
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Save plugin version in options (for future updates)
		add_option( 'sfrl_version', SFRL_VERSION );
	}
}
