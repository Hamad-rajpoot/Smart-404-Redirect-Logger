<?php
/**
 * Plugin Name: Smart 404 Redirect & Logger
 * Plugin URI:  https://wordpress.org/plugins/smart-404-redirect-logger/
 * Description: Automatically detects 404 pages, logs them, and optionally redirects users to the most similar existing page.
 * Version:     1.0.0
 * Author:      Hamad Rajpoot
 * Author URI:  https://github.com/hamadrajpoot
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smart-404-redirect-logger
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Define plugin constants
 */
define( 'SFRL_VERSION', '1.0.0' );
define( 'SFRL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SFRL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Include required files
 */
require_once SFRL_PLUGIN_DIR . 'includes/class-sfrl-activator.php';
require_once SFRL_PLUGIN_DIR . 'includes/class-sfrl-logger.php';
require_once SFRL_PLUGIN_DIR . 'includes/class-sfrl-redirect.php';
require_once SFRL_PLUGIN_DIR . 'includes/class-sfrl-admin-page.php';
require_once SFRL_PLUGIN_DIR . 'includes/class-sfrl-settings-page.php';
require_once SFRL_PLUGIN_DIR . 'includes/class-sfrl-redirect-manager.php';

/**
 * Activation hook - create custom table
 */
register_activation_hook( __FILE__, array( 'SFRL_Activator', 'activate' ) );

/**
 * Initialize plugin
 */

function sfrl_init_plugin() {
	// Initialize core classes
	new SFRL_Logger();
	new SFRL_Redirect();
	new SFRL_Admin_Page();
	new SFRL_Settings_Page();
	new SFRL_Redirect_Manager();
	
}
add_action( 'plugins_loaded', 'sfrl_init_plugin' );


