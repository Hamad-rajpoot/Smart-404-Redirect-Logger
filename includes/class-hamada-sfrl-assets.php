<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SFRL_Assets {

	public function __construct() {
		// Admin assets only
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ] );
	}

	/**
	 * Enqueue admin scripts and styles for allowed plugin pages
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function admin_assets( $hook ) {
		$allowed_pages = [
			'toplevel_page_sfrl-logs',
			'sfrl-logs_page_sfrl-redirect-manager',
		];

		// Only enqueue on allowed pages
		if ( ! in_array( $hook, $allowed_pages, true ) ) {
			return;
		}

		// --- Enqueue CSS ---
		wp_enqueue_style(
			'sfrl-admin-css',
			esc_url( SFRL_PLUGIN_URL . 'assets/css/admin.css' ),
			[],
			esc_attr( SFRL_VERSION )
		);

		// --- Enqueue JS ---
		wp_enqueue_script(
			'sfrl-admin-js',
			esc_url( SFRL_PLUGIN_URL . 'assets/js/admin.js' ),
			['jquery'],
			esc_attr( SFRL_VERSION ),
			true
		);
	}
}
