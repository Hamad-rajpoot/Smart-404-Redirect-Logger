<?php
/**
 * Handles logging of 404 page requests into the database.
 *
 * @package Smart_404_Redirect_Logger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SFRL_Logger {

	/**
	 * Constructor - hook into WordPress.
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'detect_404' ) );
	}

	/**
	 * Detect if the current request is a 404 page.
	 */
	public function detect_404() {
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

		// Get data
		$missing_url   = esc_url_raw( $_SERVER['REQUEST_URI'] ?? '' );
		$referrer      = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '';
		$ip_address    = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );
		$current_time = current_time('mysql');

		// Avoid logging WordPress admin or AJAX URLs
		if ( is_admin() || defined( 'DOING_AJAX' ) ) {
			return;
		}

		// Check if same URL was already logged recently (avoid duplicates)  
         $existing = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM $table_name WHERE missing_url = %s LIMIT 1",
            $missing_url
        )
    );

		
 if ( empty( $existing ) ) {
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

	}
}
}