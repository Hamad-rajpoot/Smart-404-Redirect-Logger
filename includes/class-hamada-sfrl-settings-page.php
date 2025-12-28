<?php
/**
 * Settings page for Smart 404 Redirect & Logger.
 *
 * @package Hamada_Smart_404_Redirect_Logger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SFRL_Settings_Page {

	/**
	 * Constructor - hooks for settings page.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Add settings submenu under 404 Logs menu.
	 */
	public function add_settings_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_submenu_page(
			'sfrl-logs',
			__( 'Smart 404 Settings', 'smart-404-redirect-logger' ),
			__( 'Settings', 'smart-404-redirect-logger' ),
			'manage_options',
			'sfrl-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {

		// Enable logging (checkbox)
		register_setting(
			'sfrl_settings_group',
			'sfrl_enable_logging',
			[
				'sanitize_callback' => function( $value ) {
					return ( $value === '1' ) ? 1 : 0;
				},
			]
		);

		// Excluded URLs (textarea)
		register_setting(
			'sfrl_settings_group',
			'sfrl_excluded_urls',
			[
				'sanitize_callback' => 'sanitize_textarea_field',
			]
		);

		// Excluded IPs (textarea)
		register_setting(
			'sfrl_settings_group',
			'sfrl_excluded_ips',
			[
				'sanitize_callback' => 'sanitize_textarea_field',
			]
		);

		// Auto delete logs (number)
		register_setting(
			'sfrl_settings_group',
			'sfrl_delete_after_days',
			[
				'sanitize_callback' => function( $value ) {
					$value = intval( $value );
					return ( $value >= 0 ) ? $value : 0;
				},
			]
		);
	}

	/**
	 * Render the settings page HTML.
	 */
	public function render_settings_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$enable_logging = get_option( 'sfrl_enable_logging', 1 );
		$excluded_urls  = get_option( 'sfrl_excluded_urls', '' );
		$excluded_ips   = get_option( 'sfrl_excluded_ips', '' );
		$delete_after   = get_option( 'sfrl_delete_after_days', 30 );
		?>

		<div class="wrap">
			<h1><?php esc_html_e( 'Smart 404 Settings', 'smart-404-redirect-logger' ); ?></h1>

			<form method="post" action="options.php">

				<?php settings_fields( 'sfrl_settings_group' ); ?>
				<?php do_settings_sections( 'sfrl_settings_group' ); ?>

				<table class="form-table" role="presentation">

					<!-- Enable Logging -->
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable Logging', 'smart-404-redirect-logger' ); ?></th>
						<td>
							<label>
								<input type="checkbox"
									name="sfrl_enable_logging"
									value="1"
									<?php checked( $enable_logging, 1 ); ?>>
								<?php esc_html_e( 'Log all 404 errors automatically.', 'smart-404-redirect-logger' ); ?>
							</label>
						</td>
					</tr>

					<!-- Excluded URLs -->
					<tr>
						<th scope="row"><?php esc_html_e( 'Exclude URLs', 'smart-404-redirect-logger' ); ?></th>
						<td>
							<textarea
								name="sfrl_excluded_urls"
								rows="3"
								cols="50"
								placeholder="/sample-page/, /privacy-policy/"><?php
									echo esc_textarea( $excluded_urls );
								?></textarea>
							<p class="description">
								<?php esc_html_e( 'Comma-separated list of URLs to exclude from logging.', 'smart-404-redirect-logger' ); ?>
							</p>
						</td>
					</tr>

					<!-- Excluded IPs -->
					<tr>
						<th scope="row"><?php esc_html_e( 'Exclude IPs', 'smart-404-redirect-logger' ); ?></th>
						<td>
							<textarea
								name="sfrl_excluded_ips"
								rows="3"
								cols="50"
								placeholder="127.0.0.1, 192.168.0.5"><?php
									echo esc_textarea( $excluded_ips );
								?></textarea>
							<p class="description">
								<?php esc_html_e( 'Comma-separated list of IP addresses to exclude from logging.', 'smart-404-redirect-logger' ); ?>
							</p>
						</td>
					</tr>

					<!-- Auto Delete Logs -->
					<tr>
						<th scope="row"><?php esc_html_e( 'Auto Delete Logs', 'smart-404-redirect-logger' ); ?></th>
						<td>
							<input
								type="number"
								name="sfrl_delete_after_days"
								value="<?php echo esc_attr( $delete_after ); ?>"
								min="0"
								style="width: 80px;">
							<?php esc_html_e( 'days after log creation', 'smart-404-redirect-logger' ); ?>
							<p class="description">
								<?php esc_html_e( 'Set how long logs should be stored. Enter 0 to disable auto deletion.', 'smart-404-redirect-logger' ); ?>
							</p>
						</td>
					</tr>

				</table>

				<?php submit_button(); ?>
			</form>
		</div>

		<?php
	}
}
