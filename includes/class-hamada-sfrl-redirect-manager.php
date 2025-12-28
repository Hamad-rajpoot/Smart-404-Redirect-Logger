<?php
/**
 * Redirect Manager: Handles manual redirects and 404 redirect logic.
 *
 * @package Hamada_Smart_404_Redirect_Logger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SFRL_Redirect_Manager {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_post_sfrl_add_redirect', [ $this, 'save_redirect' ] );
		add_action( 'admin_post_sfrl_delete_redirect', [ $this, 'delete_redirect' ] );
		add_action( 'template_redirect', [ $this, 'maybe_redirect_404' ], 5 );
	}

	/**
	 * Register submenu page under logs menu.
	 */
	public function add_menu_page() {
		add_submenu_page(
			'sfrl-logs',
			__( 'Redirect Manager', 'hamada-smart-404-redirect-logger' ),
			__( 'Redirect Manager', 'hamada-smart-404-redirect-logger' ),
			'manage_options',
			'sfrl-redirect-manager',
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Render redirect manager admin page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'hamada-smart-404-redirect-logger' ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'sfrl_manual_redirects';

		$redirects = $wpdb->get_results(
			"SELECT * FROM {$table_name} ORDER BY id DESC"
		);

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Redirect Manager', 'hamada-smart-404-redirect-logger' ); ?></h1>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'sfrl_add_redirect_nonce' ); ?>
				<input type="hidden" name="action" value="sfrl_add_redirect">

				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'From URL', 'hamada-smart-404-redirect-logger' ); ?></th>
						<td>
							<input type="text"
								name="from_url"
								class="regular-text"
								required
								placeholder="/old-page">
						</td>
					</tr>

					<tr>
						<th><?php esc_html_e( 'To URL', 'hamada-smart-404-redirect-logger' ); ?></th>
						<td>
							<input type="text"
								name="to_url"
								class="regular-text"
								required
								placeholder="/new-page">
						</td>
					</tr>
				</table>

				<?php submit_button( __( 'Add Redirect', 'hamada-smart-404-redirect-logger' ) ); ?>
			</form>

			<h2><?php esc_html_e( 'Existing Redirects', 'hamada-smart-404-redirect-logger' ); ?></h2>

			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ID', 'hamada-smart-404-redirect-logger' ); ?></th>
						<th><?php esc_html_e( 'From URL', 'hamada-smart-404-redirect-logger' ); ?></th>
						<th><?php esc_html_e( 'To URL', 'hamada-smart-404-redirect-logger' ); ?></th>
						<th><?php esc_html_e( 'Date Added', 'hamada-smart-404-redirect-logger' ); ?></th>
						<th><?php esc_html_e( 'Action', 'hamada-smart-404-redirect-logger' ); ?></th>
					</tr>
				</thead>

				<tbody>
					<?php if ( ! empty( $redirects ) ) : ?>
						<?php foreach ( $redirects as $redirect ) : ?>
							<tr>
								<td><?php echo esc_html( $redirect->id ); ?></td>
								<td><?php echo esc_html( $redirect->from_url ); ?></td>
								<td><?php echo esc_html( $redirect->to_url ); ?></td>
								<td><?php echo esc_html( $redirect->date_added ); ?></td>
								<td>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
										<?php wp_nonce_field( 'sfrl_delete_redirect_nonce' ); ?>
										<input type="hidden" name="action" value="sfrl_delete_redirect">
										<input type="hidden" name="redirect_id" value="<?php echo esc_attr( $redirect->id ); ?>">
										<button type="submit" class="button-link-delete">
											<?php esc_html_e( 'Delete', 'hamada-smart-404-redirect-logger' ); ?>
										</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="5"><?php esc_html_e( 'No redirects found.', 'hamada-smart-404-redirect-logger' ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Save new redirect.
	 */
	public function save_redirect() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized request.', 'hamada-smart-404-redirect-logger' ) );
		}

		check_admin_referer( 'sfrl_add_redirect_nonce' );

		global $wpdb;
		$table_name = $wpdb->prefix . 'sfrl_manual_redirects';

		$from_url = '/' . ltrim( sanitize_text_field( wp_unslash( $_POST['from_url'] ?? '' ) ), '/' );
		$to_url   = esc_url_raw( wp_unslash( $_POST['to_url'] ?? '' ) );

		if ( ! empty( $from_url ) && ! empty( $to_url ) ) {
			$wpdb->insert(
				$table_name,
				[
					'from_url' => $from_url,
					'to_url'   => $to_url,
				],
				[ '%s', '%s' ]
			);
		}

		wp_safe_redirect( admin_url( 'admin.php?page=sfrl-redirect-manager' ) );
		exit;
	}

	/**
	 * Delete redirect entry.
	 */
	public function delete_redirect() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized request.', 'hamada-smart-404-redirect-logger' ) );
		}

		check_admin_referer( 'sfrl_delete_redirect_nonce' );

		global $wpdb;
		$table_name = $wpdb->prefix . 'sfrl_manual_redirects';

		$id = isset( $_POST['redirect_id'] ) ? absint( $_POST['redirect_id'] ) : 0;

		if ( $id > 0 ) {
			$wpdb->delete( $table_name, [ 'id' => $id ], [ '%d' ] );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=sfrl-redirect-manager' ) );
		exit;
	}

	/**
	 * Handle 404 redirects.
	 */
	public function maybe_redirect_404() {
		if ( ! is_404() ) {
			return;
		}

		$request_uri  = trim( sanitize_text_field( $_SERVER['REQUEST_URI'] ?? '' ), '/' );
		$request_path = '/' . $request_uri;

		$manual_redirect = $this->check_manual_redirect( $request_path );

		if ( $manual_redirect ) {
			wp_safe_redirect( $manual_redirect, 301 );
			exit;
		}
	}

	/**
	 * Check database for manual redirect match.
	 *
	 * @param string $url Requested URL.
	 * @return false|string
	 */
	private function check_manual_redirect( $url ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sfrl_manual_redirects';
		$url        = rtrim( sanitize_text_field( $url ), '/' );

		$redirect_to = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT to_url FROM {$table_name} WHERE from_url = %s LIMIT 1",
				$url
			)
		);

		return $redirect_to ?: false;
	}
}
