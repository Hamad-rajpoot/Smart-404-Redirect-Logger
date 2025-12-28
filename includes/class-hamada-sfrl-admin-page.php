<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SFRL_Admin_Page {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		// Form handlers
		add_action( 'admin_post_sfrl_clear_logs', [ __CLASS__, 'handle_clear_logs' ] );
		add_action( 'admin_post_sfrl_bulk_delete', [ __CLASS__, 'handle_bulk_delete' ] );
		add_action( 'admin_post_sfrl_export_csv', [ __CLASS__, 'handle_export_csv' ] );
	}

	/**
	 * Add admin menu page.
	 */
	public function add_menu_page() {
		add_menu_page(
			esc_html__( 'Smart 404 Logs', 'hamada-smart-404-redirect-logger' ),
			esc_html__( 'Smart 404 Logs', 'hamada-smart-404-redirect-logger' ),
			'manage_options',
			'sfrl-logs',
			[ $this, 'render_page' ],
			'dashicons-admin-site'
		);
	}

	/**
	 * Render the admin page.
	 */
	public function render_page() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'sfrl_404_logs';

		$per_page = 10;
		$paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$offset   = ( $paged - 1 ) * $per_page;

		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

		$where = '';
		if ( $search ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$where = $wpdb->prepare( "WHERE missing_url LIKE %s OR referrer LIKE %s OR redirected_to LIKE %s", $like, $like, $like );
		}

		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM `$table_name` $where" );
		$logs        = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM `$table_name` $where ORDER BY date_logged DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			)
		);

		$total_pages = ceil( $total_items / $per_page );
		?>

		<div class="wrap">
			<h1><?php esc_html_e( 'Smart 404 Logs', 'hamada-smart-404-redirect-logger' ); ?></h1>

			<?php if ( ! empty( $_GET['sfrl_notice'] ) ) : ?>
				<div class="notice notice-success">
					<p><?php echo esc_html( wp_unslash( $_GET['sfrl_notice'] ) ); ?></p>
				</div>
			<?php endif; ?>

			<!-- Search -->
			<form method="get" style="margin-bottom: 10px;">
				<input type="hidden" name="page" value="sfrl-logs">
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search URL or Referrer', 'hamada-smart-404-redirect-logger' ); ?>">
				<button class="button"><?php esc_html_e( 'Search', 'hamada-smart-404-redirect-logger' ); ?></button>
			</form>

			<!-- Bulk Form -->
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'sfrl_bulk_action', 'sfrl_nonce' ); ?>
				<input type="hidden" name="action" value="sfrl_bulk_delete">

				<div style="margin-bottom: 10px;">
					<button class="button button-secondary" type="submit"><?php esc_html_e( 'Delete Selected', 'hamada-smart-404-redirect-logger' ); ?></button>
					<a class="button button-secondary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=sfrl_clear_logs' ), 'sfrl_clear_logs_nonce' ) ); ?>">
						<?php esc_html_e( 'Clear Logs', 'hamada-smart-404-redirect-logger' ); ?>
					</a>
					<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=sfrl_export_csv' ), 'sfrl_export_csv_nonce' ) ); ?>">
						<?php esc_html_e( 'Export CSV', 'hamada-smart-404-redirect-logger' ); ?>
					</a>
				</div>

				<table class="widefat striped">
					<thead>
						<tr>
							<th><input type="checkbox" id="sfrl-select-all"></th>
							<th><?php esc_html_e( 'ID', 'hamada-smart-404-redirect-logger' ); ?></th>
							<th><?php esc_html_e( 'Missing URL', 'hamada-smart-404-redirect-logger' ); ?></th>
							<th><?php esc_html_e( 'Referrer', 'hamada-smart-404-redirect-logger' ); ?></th>
							<th><?php esc_html_e( 'Redirected To', 'hamada-smart-404-redirect-logger' ); ?></th>
							<th><?php esc_html_e( 'IP Address', 'hamada-smart-404-redirect-logger' ); ?></th>
							<th><?php esc_html_e( 'Date Logged', 'hamada-smart-404-redirect-logger' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( $logs ) : foreach ( $logs as $log ) : ?>
							<tr>
								<td><input type="checkbox" name="log_ids[]" value="<?php echo esc_attr( $log->id ); ?>"></td>
								<td><?php echo esc_html( $log->id ); ?></td>
								<td><?php echo esc_html( $log->missing_url ); ?></td>
								<td><?php echo $log->referrer ? esc_html( $log->referrer ) : '—'; ?></td>
								<td><?php echo $log->redirected_to ? esc_html( $log->redirected_to ) : '—'; ?></td>
								<td><?php echo esc_html( $log->ip_address ); ?></td>
								<td><?php echo esc_html( $log->date_logged ); ?></td>
							</tr>
						<?php endforeach; else : ?>
							<tr><td colspan="7"><?php esc_html_e( 'No logs found', 'hamada-smart-404-redirect-logger' ); ?></td></tr>
						<?php endif; ?>
					</tbody>
				</table>

			</form>

			<!-- Pagination -->
			<?php if ( $total_pages > 1 ) : ?>
				<div class="sfrl-pagination">
					<?php
					$base_url = remove_query_arg( ['paged', 'sfrl_notice'] );
					for ( $i = 1; $i <= $total_pages; $i++ ) {
						$url    = add_query_arg( ['paged' => $i], $base_url );
						$active = ( $i === $paged ) ? 'sfrl-page-active' : '';
						echo '<a class="sfrl-page-btn ' . esc_attr( $active ) . '" href="' . esc_url( $url ) . '">' . esc_html( $i ) . '</a>';
					}
					?>
				</div>
			<?php endif; ?>

		</div>

		<?php
	}

	/* -----------------------------
	 * HANDLERS
	 ------------------------------*/

	public static function handle_clear_logs() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'hamada-smart-404-redirect-logger' ) );
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sfrl_clear_logs_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'hamada-smart-404-redirect-logger' ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'sfrl_404_logs';
		$wpdb->query( "TRUNCATE TABLE $table" );

		wp_safe_redirect(
			admin_url( 'admin.php?page=sfrl-logs&sfrl_notice=' . rawurlencode( esc_html__( 'Logs cleared!', 'hamada-smart-404-redirect-logger' ) ) )
		);
		exit;
	}

	public static function handle_bulk_delete() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'hamada-smart-404-redirect-logger' ) );
		}

		if ( ! isset( $_POST['sfrl_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sfrl_nonce'] ) ), 'sfrl_bulk_action' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'hamada-smart-404-redirect-logger' ) );
		}

		if ( ! empty( $_POST['log_ids'] ) ) {
			global $wpdb;
			$ids   = array_map( 'absint', wp_unslash( $_POST['log_ids'] ) );
			$table = $wpdb->prefix . 'sfrl_404_logs';

			$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM $table WHERE id IN ($placeholders)",
					$ids
				)
			);
		}

		wp_safe_redirect(
			admin_url( 'admin.php?page=sfrl-logs&sfrl_notice=' . rawurlencode( esc_html__( 'Selected logs deleted!', 'hamada-smart-404-redirect-logger' ) ) )
		);
		exit;
	}

	public static function handle_export_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'hamada-smart-404-redirect-logger' ) );
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sfrl_export_csv_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'hamada-smart-404-redirect-logger' ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'sfrl_404_logs';
		$rows  = $wpdb->get_results( "SELECT * FROM $table ORDER BY date_logged DESC", ARRAY_A );

		if ( empty( $rows ) ) {
			wp_safe_redirect(
				admin_url( 'admin.php?page=sfrl-logs&sfrl_notice=' . rawurlencode( esc_html__( 'No data to export!', 'hamada-smart-404-redirect-logger' ) ) )
			);
			exit;
		}

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="smart-404-logs.csv"' );

		$fh = fopen( 'php://output', 'w' );

		fputcsv( $fh, [ 'ID', 'Missing URL', 'Referrer', 'Redirected To', 'IP Address', 'Date Logged' ] );

		foreach ( $rows as $row ) {
			fputcsv( $fh, $row );
		}

		fclose( $fh );
		exit;
	}
}
