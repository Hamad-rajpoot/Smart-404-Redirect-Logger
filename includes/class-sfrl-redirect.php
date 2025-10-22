<?php
/**
 * Handles automatic redirection of 404 pages to similar existing pages or posts.
 *
 * @package Smart_404_Redirect_Logger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SFRL_Redirect {

	/**
	 * Constructor - hook into template_redirect to handle smart redirects.
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'maybe_redirect_404' ), 20 );
	}

	/**
	 * If a 404 page is detected, try to find a similar post/page and redirect.
	 */
	public function maybe_redirect_404() {
		if ( ! is_404() ) {
			return;
		}

		$request_uri = trim( $_SERVER['REQUEST_URI'], '/' );
		if ( empty( $request_uri ) ) {
			return;
		}

		// Extract slug from URL (remove query parameters)
		$slug = sanitize_title( basename( strtok( $request_uri, '?' ) ) );

		// Search for similar posts or pages
		$redirect_url = $this->find_similar_post_url( $slug );

		if ( $redirect_url ) {
			// Update redirected_to column in the log (optional)
			global $wpdb;
			$table_name = $wpdb->prefix . 'sfrl_404_logs';
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE $table_name SET redirected_to = %s WHERE missing_url = %s ORDER BY id DESC LIMIT 1",
					$redirect_url,
					'/' . $request_uri
				)
			);

			// Perform redirect
			wp_redirect( $redirect_url, 301 );
			exit;
		}
	}

	/**
	 * Try to find the most similar post or page based on slug similarity.
	 *
	 * @param string $slug The slug of the missing URL.
	 * @return string|false URL of the similar post/page if found, false otherwise.
	 */
	private function find_similar_post_url( $slug ) {
		if ( empty( $slug ) ) {
			return false;
		}

		$args = array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		$posts = get_posts( $args );
		$best_match = array(
			'score' => 0,
			'id'    => 0,
		);

		foreach ( $posts as $post_id ) {
			$post_slug = basename( get_permalink( $post_id ) );
			similar_text( $slug, $post_slug, $percent );

			if ( $percent > $best_match['score'] ) {
				$best_match = array(
					'score' => $percent,
					'id'    => $post_id,
				);
			}
		}

		// If similarity score is more than 70%, redirect to that page.
		if ( $best_match['score'] > 70 ) {
			return get_permalink( $best_match['id'] );
		}

		return false;
	}
}
