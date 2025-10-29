<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SFRL_Settings_Page {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function add_settings_page() {
		add_submenu_page(
			'sfrl-logs',
			__( 'Smart 404 Settings', 'sfrl' ),
			__( 'Settings', 'sfrl' ),
			'manage_options',
			'sfrl-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	public function register_settings() {
		register_setting( 'sfrl_settings_group', 'sfrl_enable_logging' );
		register_setting( 'sfrl_settings_group', 'sfrl_excluded_urls' );
		register_setting( 'sfrl_settings_group', 'sfrl_excluded_ips' );
		register_setting( 'sfrl_settings_group', 'sfrl_delete_after_days' );
	}

	public function render_settings_page() {
		$enable_logging = get_option( 'sfrl_enable_logging', 1 );
		$excluded_urls = get_option( 'sfrl_excluded_urls', '' );
		$excluded_ips  = get_option( 'sfrl_excluded_ips', '' );
		$delete_after  = get_option( 'sfrl_delete_after_days', 30 );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Smart 404 Settings', 'sfrl' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'sfrl_settings_group' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable Logging', 'sfrl' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="sfrl_enable_logging" value="1" <?php checked( $enable_logging, 1 ); ?>>
								<?php esc_html_e( 'Log 404 redirects automatically', 'sfrl' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Exclude URLs', 'sfrl' ); ?></th>
						<td>
							<textarea name="sfrl_excluded_urls" rows="3" cols="50" placeholder="/sample-page/, /privacy-policy/"><?php echo esc_textarea( $excluded_urls ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Enter comma-separated URLs to exclude from logging.', 'sfrl' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Exclude IPs', 'sfrl' ); ?></th>
						<td>
							<textarea name="sfrl_excluded_ips" rows="3" cols="50" placeholder="127.0.0.1, 192.168.0.5"><?php echo esc_textarea( $excluded_ips ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Enter comma-separated IP addresses to exclude from logging.', 'sfrl' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Auto Delete Logs', 'sfrl' ); ?></th>
						<td>
							<input type="number" name="sfrl_delete_after_days" value="<?php echo esc_attr( $delete_after ); ?>" min="1" style="width: 80px;"> 
							<?php esc_html_e( 'days after entry is created', 'sfrl' ); ?>
							<p class="description"><?php esc_html_e( 'Set how long logs should be kept. Leave blank to disable auto-deletion.', 'sfrl' ); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
