<?php
namespace FilterFlow_Posts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ), 20 );
	}

	public function init(): void {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.20.0', '<' ) ) {
			return;
		}

		new Ajax();

		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_widget_category' ) );
	}

	public function register_assets(): void {
		$asset_version = FILTERFLOW_POSTS_VERSION . '-' . FILTERFLOW_POSTS_BUILD;
		wp_register_style(
			'filterflow-posts',
			FILTERFLOW_POSTS_URL . 'assets/css/filterflow-posts.css',
			array(),
			$asset_version
		);

		wp_register_script(
			'filterflow-posts',
			FILTERFLOW_POSTS_URL . 'assets/js/filterflow-posts.js',
			array(),
			$asset_version,
			true
		);

		wp_localize_script(
			'filterflow-posts',
			'FilterFlowPosts',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'filterflow_posts' ),
				'i18n'    => array(
					'error'   => __( 'The posts could not be loaded. Please try again.', 'filterflow-posts' ),
					'loading' => __( 'Loading posts…', 'filterflow-posts' ),
					/* translators: %d: number of posts found. */
					'postFound' => __( '%d post found.', 'filterflow-posts' ),
					/* translators: %d: number of posts found. */
					'postsFound' => __( '%d posts found.', 'filterflow-posts' ),
					/* translators: %s: active category name. */
					'currentCategory' => __( 'Current category: %s. Open category filters.', 'filterflow-posts' ),
					/* translators: %s: label for the All filter. */
					'allSelected' => __( '%s, selected.', 'filterflow-posts' ),
					/* translators: %s: label for the All filter. */
					'showAll' => __( 'Show %s posts.', 'filterflow-posts' ),
				),
			)
		);
	}

	public function register_widgets( $widgets_manager ): void {
		require_once FILTERFLOW_POSTS_PATH . 'includes/class-filterflow-widget.php';
		$widgets_manager->register( new Widget() );
	}

	public function register_widget_category( $elements_manager ): void {
		$elements_manager->add_category(
			'filterflow',
			array(
				'title' => __( 'FilterFlow', 'filterflow-posts' ),
				'icon'  => 'eicon-filter',
			)
		);
	}

}