<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SFRL_Admin_Page {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
        add_action( 'admin_post_sfrl_clear_logs', [ __CLASS__, 'handle_clear_logs' ] );
        add_action( 'admin_post_sfrl_bulk_delete', [ __CLASS__, 'handle_bulk_delete' ] );
        add_action( 'admin_post_sfrl_export_csv', [ __CLASS__, 'handle_export_csv' ] );
    }

    public function add_menu_page() {
        add_menu_page(
            __( 'Smart 404 Logs', 'sfrl' ),
            __( 'Smart 404 Logs', 'sfrl' ),
            'manage_options',
            'sfrl-logs',
            [ $this, 'render_page' ],
            'dashicons-admin-site'
        );
    }

    public function render_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sfrl_404_logs';

        // Pagination
        $per_page = 4;
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($paged - 1) * $per_page;

        // Search filter
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $where = $search ? $wpdb->prepare(
            "WHERE missing_url LIKE %s OR referrer LIKE %s OR redirected_to LIKE %s",
            "%$search%", "%$search%", "%$search%"
        ) : '';

        $total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $where" );
        $logs = $wpdb->get_results( "SELECT * FROM $table_name $where ORDER BY date_logged DESC LIMIT $per_page OFFSET $offset" );
        $total_pages = ceil($total_items / $per_page);

        // Admin notice
        if ( !empty($_GET['sfrl_notice']) ) {
            echo '<div class="notice notice-success"><p>' . esc_html($_GET['sfrl_notice']) . '</p></div>';
        }
        ?>

        <div class="wrap">
            <h1><?php _e('Smart 404 Logs', 'sfrl'); ?></h1>

            <form method="get" style="margin-bottom:10px;">
                <input type="hidden" name="page" value="sfrl-logs" />
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search URL or Referrer" />
                <button class="button"><?php _e('Search'); ?></button>
            </form>

            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('sfrl_bulk_action', 'sfrl_nonce'); ?>
                <input type="hidden" name="action" value="sfrl_bulk_delete">

                <div style="margin-bottom:10px;">
                    <button type="submit" class="button button-secondary" name="bulk_delete"><?php _e('Delete Selected', 'sfrl'); ?></button>
                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=sfrl_clear_logs'), 'sfrl_clear_logs_nonce'); ?>" class="button button-secondary"><?php _e('Clear Logs', 'sfrl'); ?></a>
                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=sfrl_export_csv'), 'sfrl_export_csv_nonce'); ?>" class="button button-primary"><?php _e('Export CSV', 'sfrl'); ?></a>
                </div>

                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="sfrl-select-all"></th>
                            <th>ID</th>
                            <th>Missing URL</th>
                            <th>Referrer</th>
                            <th>Redirected To</th>
                            <th>IP Address</th>
                            <th>Date Logged</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( $logs ) : foreach ( $logs as $log ) : ?>
                            <tr>
                                <td><input type="checkbox" name="log_ids[]" value="<?php echo esc_attr($log->id); ?>"></td>
                                <td><?php echo esc_html($log->id); ?></td>
                                <td><?php echo esc_html($log->missing_url); ?></td>
                                <td><?php echo esc_html($log->referrer ?: '—'); ?></td>
                                <td><?php echo esc_html($log->redirected_to ?: '—'); ?></td>
                                <td><?php echo esc_html($log->ip_address); ?></td>
                                <td><?php echo esc_html($log->date_logged); ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="7"><?php _e('No logs found', 'sfrl'); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>

            <script>
                document.getElementById('sfrl-select-all').addEventListener('change', function(){
                    const checkboxes = document.querySelectorAll('input[name="log_ids[]"]');
                    checkboxes.forEach(cb => cb.checked = this.checked);
                });
            </script>

    <?php if ( $total_pages > 1 ) : ?>
    <div class="sfrl-pagination" style="margin-top:20px; text-align:center;">
        <?php
        $base_url = remove_query_arg( ['paged', 'sfrl_notice'] );
        $prev_page = max( 1, $paged - 1 );
        $next_page = min( $total_pages, $paged + 1 );

        // Previous button
        if ( $paged > 1 ) {
            $prev_url = add_query_arg( ['paged' => $prev_page], $base_url );
            echo '<a class="sfrl-page-btn" href="' . esc_url( $prev_url ) . '">&laquo; Previous</a>';
        }

        // Numbered pages
        for ( $i = 1; $i <= $total_pages; $i++ ) {
            $url = add_query_arg( ['paged' => $i], $base_url );
            $active = ( $i === $paged ) ? 'sfrl-page-active' : '';
            echo '<a class="sfrl-page-btn ' . esc_attr( $active ) . '" href="' . esc_url( $url ) . '">' . esc_html( $i ) . '</a>';
        }

        // Next button
        if ( $paged < $total_pages ) {
            $next_url = add_query_arg( ['paged' => $next_page], $base_url );
            echo '<a class="sfrl-page-btn" href="' . esc_url( $next_url ) . '">Next &raquo;</a>';
        }
        ?>
    </div>

    <style>
        .sfrl-page-btn {
            display: inline-block;
            margin: 0 4px;
            padding: 6px 10px;
            background: #f0f0f1;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            text-decoration: none;
            color: #2271b1;
            font-weight: 500;
        }
        .sfrl-page-btn:hover {
            background: #e5f2ff;
            border-color: #2271b1;
        }
        .sfrl-page-active {
            background: #2271b1;
            color: #fff !important;
            border-color: #2271b1;
        }
    </style>
<?php endif; ?>
        </div>
        <?php
    }

    public static function handle_clear_logs() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Unauthorized action.', 'smart-404-redirect-logger' ) );
	}

	// Make sure nonce exists and is valid
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'sfrl_clear_logs_nonce' ) ) {
		wp_die( __( 'Security check failed.', 'smart-404-redirect-logger' ) );
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'sfrl_404_logs';
	$wpdb->query( "TRUNCATE TABLE {$table_name}" );

	wp_safe_redirect( admin_url( 'admin.php?page=sfrl-logs&sfrl_notice=' . rawurlencode( 'All logs cleared!' ) ) );
	exit;
}

/**
 * Handle Bulk Delete (safe nonce check)
 */
public static function handle_bulk_delete() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Unauthorized action.', 'smart-404-redirect-logger' ) );
	}

	if ( ! isset( $_POST['sfrl_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['sfrl_nonce'] ), 'sfrl_bulk_action' ) ) {
		wp_die( __( 'Security check failed.', 'smart-404-redirect-logger' ) );
	}

	if ( ! empty( $_POST['log_ids'] ) && is_array( $_POST['log_ids'] ) ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'sfrl_404_logs';
		$ids        = array_map( 'absint', wp_unslash( $_POST['log_ids'] ) );

		// Build safe IN clause
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$sql = $wpdb->prepare( "DELETE FROM {$table_name} WHERE id IN ($placeholders)", $ids );
		$wpdb->query( $sql );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=sfrl-logs&sfrl_notice=' . rawurlencode( 'Selected logs deleted!' ) ) );
	exit;
}

/**
 * Handle CSV Export (safe nonce check)
 */
public static function handle_export_csv() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Unauthorized action.', 'smart-404-redirect-logger' ) );
	}

	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'sfrl_export_csv_nonce' ) ) {
		wp_die( __( 'Security check failed.', 'smart-404-redirect-logger' ) );
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'sfrl_404_logs';
	$logs       = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY date_logged DESC", ARRAY_A );

	if ( empty( $logs ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=sfrl-logs&sfrl_notice=' . rawurlencode( 'No data to export!' ) ) );
		exit;
	}

	// Send CSV
	header( 'Content-Type: text/csv; charset=' . get_option( 'blog_charset' ) );
	header( 'Content-Disposition: attachment; filename="smart-404-logs.csv"' );
	$fh = fopen( 'php://output', 'w' );
	fputcsv( $fh, array( 'ID', 'Missing URL', 'Referrer', 'Redirected To', 'IP Address', 'Date Logged' ) );
	foreach ( $logs as $row ) {
		fputcsv( $fh, array( $row['id'], $row['missing_url'], $row['referrer'], $row['redirected_to'], $row['ip_address'], $row['date_logged'] ) );
	}
	fclose( $fh );
	exit;
}
}
