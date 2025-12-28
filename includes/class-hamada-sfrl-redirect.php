<?php
/**
 * Handles automatic redirection of 404 pages to similar existing pages or posts.
 *
 * @package Hamada_Smart_404_Redirect_Logger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SFRL_Redirect {

	/**
	 * Constructor - hook into template_redirect to handle smart redirects.
	 */
	public function __construct() {
		add_action( 'template_redirect', [ $this, 'maybe_redirect_404' ], 20 );
	}

	/**
	 * If a 404 page is detected, try to find a similar post/page and redirect.
	 */
	public function maybe_redirect_404() {

		// Must be 404 page
		if ( ! is_404() ) {
			return;
		}

		// Allow admin to disable smart matching
		$enabled = apply_filters( 'sfrl_enable_smart_redirect', true );

		if ( ! $enabled ) {
			return;
		}

		// Sanitize the request URL
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		$request_uri = trim( $request_uri, '/' );

		if ( empty( $request_uri ) ) {
			return;
		}

		// Extract slug (remove query parameters)
		$clean_path = strtok( $request_uri, '?' );
		$slug       = sanitize_title( basename( $clean_path ) );

		if ( empty( $slug ) ) {
			return;
		}

		// Find similar post or page
		$redirect_url = $this->find_similar_post_url( $slug );

		// If found, validate and redirect
		if ( $redirect_url && filter_var( $redirect_url, FILTER_VALIDATE_URL ) ) {

			// Update redirect log safely
			global $wpdb;
			$table_name = $wpdb->prefix . 'sfrl_404_logs';

			$wpdb->update(
				$table_name,
				[ 'redirected_to' => esc_url_raw( $redirect_url ) ],
				[ 'missing_url'   => '/' . $clean_path ],
				[ '%s' ],
				[ '%s' ]
			);

			// Perform safe redirect
			wp_safe_redirect( $redirect_url, 301 );
			exit;
		}
	}

	/**
	 * Try to find the most similar post/page based on slug or title similarity.
	 *
	 * @param string $slug Slug extracted from the missing URL.
	 * @return string|false URL of the similar post/page or false.
	 */
	private function find_similar_post_url( $slug ) {

		if ( empty( $slug ) ) {
			return false;
		}

		$args = [
			'post_type'      => [ 'post', 'page' ],
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		];

		$posts = get_posts( $args );

		if ( empty( $posts ) ) {
			return false;
		}

		$best_match = [
			'score' => 0,
			'id'    => 0,
		];

		foreach ( $posts as $post_id ) {

			$post_slug  = sanitize_title( basename( get_permalink( $post_id ) ) );
			$post_title = sanitize_title( get_the_title( $post_id ) );

			// Compare slug and title similarity
			similar_text( $slug, $post_slug, $slug_similarity );
			similar_text( $slug, $post_title, $title_similarity );

			$overall_score = max( $slug_similarity, $title_similarity );

			// Track highest match
			if ( $overall_score > $best_match['score'] ) {
				$best_match = [
					'score' => $overall_score,
					'id'    => $post_id,
				];
			}
		}

		// Redirect only if similarity is high enough
		if ( $best_match['score'] > 65 ) {
			return get_permalink( $best_match['id'] );
		}

		return false;
	}
}
