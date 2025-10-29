<?php
/**
 * Handles plugin activation tasks such as creating database tables.
 *
 * @package Smart_404_Redirect_Logger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class SFRL_Redirect_Manager {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_post_sfrl_add_redirect', [ $this, 'save_redirect' ] );
		add_action( 'admin_post_sfrl_delete_redirect', [ $this, 'delete_redirect' ] );
		add_action( 'template_redirect', [ $this, 'maybe_redirect_404' ], 5 );
	}

	public function add_menu_page() {
		add_submenu_page(
			'sfrl-logs',
			'Redirect Manager',
			'Redirect Manager',
			'manage_options',
			'sfrl-redirect-manager',
			[ $this, 'render_page' ]
		);
	}

	public function render_page() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'sfrl_manual_redirects';
		$redirects  = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC" );

		?>
		<div class="wrap">
			<h1>Redirect Manager</h1>
			<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
				<?php wp_nonce_field( 'sfrl_add_redirect_nonce' ); ?>
				<input type="hidden" name="action" value="sfrl_add_redirect">
				
				<table class="form-table">
					<tr>
						<th>From URL</th>
						<td><input type="text" name="from_url" class="regular-text" required placeholder="/old-page"></td>
					</tr>
					<tr>
						<th>To URL</th>
						<td><input type="text" name="to_url" class="regular-text" required placeholder="/new-page"></td>
					</tr>
				</table>

				<?php submit_button( 'Add Redirect' ); ?>
			</form>

			<h2>Existing Redirects</h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>ID</th>
						<th>From URL</th>
						<th>To URL</th>
						<th>Date Added</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $redirects as $redirect ) : ?>
						<tr>
							<td><?php echo esc_html( $redirect->id ); ?></td>
							<td><?php echo esc_html( $redirect->from_url ); ?></td>
							<td><?php echo esc_html( $redirect->to_url ); ?></td>
							<td><?php echo esc_html( $redirect->date_added ); ?></td>
							<td>
								<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display:inline;">
									<?php wp_nonce_field( 'sfrl_delete_redirect_nonce' ); ?>
									<input type="hidden" name="action" value="sfrl_delete_redirect">
									<input type="hidden" name="redirect_id" value="<?php echo esc_attr( $redirect->id ); ?>">
									<button type="submit" class="button-link-delete">Delete</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function save_redirect() {
		check_admin_referer( 'sfrl_add_redirect_nonce' );

		global $wpdb;
		$table_name = $wpdb->prefix . 'sfrl_manual_redirects';
		$from_url = sanitize_text_field( $_POST['from_url'] );
		$to_url   = esc_url_raw( $_POST['to_url'] );

		if ( ! empty( $from_url ) && ! empty( $to_url ) ) {
			$wpdb->insert( $table_name, [
				'from_url' => $from_url,
				'to_url'   => $to_url,
			] );
		}

		wp_redirect( admin_url( 'admin.php?page=sfrl-redirect-manager' ) );
		exit;
	}

	public function delete_redirect() {
		check_admin_referer( 'sfrl_delete_redirect_nonce' );

		global $wpdb;
		$table_name = $wpdb->prefix . 'sfrl_manual_redirects';
		$id = intval( $_POST['redirect_id'] );
		$wpdb->delete( $table_name, [ 'id' => $id ] );

		wp_redirect( admin_url( 'admin.php?page=sfrl-redirect-manager' ) );
		exit;
	}

/**
 * Handle 404 redirects and apply manual redirects if found.
 */
public function maybe_redirect_404() {

    // If not a 404 page, do nothing.
    if ( ! is_404() ) {
        return;
    }

    // Get the requested URL path without leading/trailing slashes
    $request_uri = trim( $_SERVER['REQUEST_URI'] ?? '', '/' );

    // Convert requested URL to the format stored in DB (like: /old-page)
    $request_path = '/' . $request_uri;

    //  STEP 1: Check Manual Redirect Table First
    $manual_redirect = $this->check_manual_redirect( $request_path );

    // If manual redirect exists → perform redirect
    if ( $manual_redirect ) {
        wp_redirect( $manual_redirect, 301 );
        exit;
    }

    // STEP 2: If no manual redirect → smart redirect logic will run (if added below)
    // (You will continue your similarity redirect logic after this block)
}


/**
 * Check for matching manual redirect entry in the database.
 *
 * @param string $url The requested URL (example: /old-page).
 * @return string|false Returns new URL if found, otherwise false.
 */
private function check_manual_redirect( $url ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'sfrl_manual_redirects';

    // Normalize: Remove trailing slash to avoid mismatch ( /page/ == /page )
    $url = rtrim( $url, '/' );

    // Look for redirect entry in database
    $redirect_to = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT to_url FROM $table_name WHERE from_url = %s LIMIT 1",
            $url
        )
    );

    // If match found → return redirect target
    if ( $redirect_to ) {
        return $redirect_to;
    }

    // No redirect found
    return false;
}
}