<?php
namespace FilterFlow_Posts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {
	public function __construct() {
		add_action( 'wp_ajax_filterflow_load_posts', array( $this, 'load_posts' ) );
		add_action( 'wp_ajax_nopriv_filterflow_load_posts', array( $this, 'load_posts' ) );
	}

	public function load_posts(): void {
		// This endpoint is read-only. Authenticated requests must carry a valid nonce.
		// Guest requests remain cache-friendly and are restricted by strict allow-lists below.
		$nonce = isset( $_POST['nonce'] ) && is_scalar( $_POST['nonce'] )
			? sanitize_text_field( wp_unslash( (string) $_POST['nonce'] ) )
			: '';

		if ( is_user_logged_in() && ! wp_verify_nonce( $nonce, 'filterflow_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'filterflow-posts' ) ), 403 );
		}

		$raw_settings = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! is_string( $raw_settings ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid widget settings.', 'filterflow-posts' ) ), 400 );
		}

		if ( strlen( $raw_settings ) > 10000 ) {
			wp_send_json_error( array( 'message' => __( 'The request is too large.', 'filterflow-posts' ) ), 413 );
		}

		$settings = json_decode( $raw_settings, true );

		if ( ! is_array( $settings ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid widget settings.', 'filterflow-posts' ) ), 400 );
		}

		$settings = Renderer::sanitize_settings( $settings );
		$term_value = isset( $_POST['term_id'] ) && is_scalar( $_POST['term_id'] ) ? wp_unslash( $_POST['term_id'] ) : 0;
		$page_value = isset( $_POST['page'] ) && is_scalar( $_POST['page'] ) ? wp_unslash( $_POST['page'] ) : 1;
		$term_id    = absint( $term_value );
		$page       = min( 500, max( 1, absint( $page_value ) ) );

		if ( $term_id && ! term_exists( $term_id, 'category' ) ) {
			$term_id = 0;
		}

		$query = Renderer::get_query( $settings, $term_id, $page );
		$html  = Renderer::render_posts( $query, $settings );

		wp_send_json_success(
			array(
				'html'       => $html,
				'pagination' => Renderer::render_pagination( $query, $settings, $page ),
				'maxPages'   => (int) $query->max_num_pages,
				'foundPosts' => (int) $query->found_posts,
				'page'       => $page,
			)
		);
	}
}
