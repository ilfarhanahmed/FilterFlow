<?php
/**
 * Plugin Name:       FilterFlow Posts
 * Plugin URI:        https://farhan.ch/filterflow
 * Description:       A responsive, AJAX-powered filterable posts grid widget for Elementor.
 * Version:           1.0.0
 * Author:            Farhan Ahmed
 * Author URI:        https://farhan.ch/
 * Text Domain:       filterflow-posts
 * Domain Path:       /languages
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Requires Plugins:  elementor
 * Elementor requires at least: 3.20.0
 * Elementor tested up to: 4.2.1
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FILTERFLOW_POSTS_VERSION', '1.0.0' );
// Internal build identifier used only for asset cache busting.
define( 'FILTERFLOW_POSTS_BUILD', '20260804.1455' );
define( 'FILTERFLOW_POSTS_FILE', __FILE__ );
define( 'FILTERFLOW_POSTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'FILTERFLOW_POSTS_URL', plugin_dir_url( __FILE__ ) );

require_once FILTERFLOW_POSTS_PATH . 'includes/class-filterflow-renderer.php';
require_once FILTERFLOW_POSTS_PATH . 'includes/class-filterflow-ajax.php';
require_once FILTERFLOW_POSTS_PATH . 'includes/class-filterflow-plugin.php';

FilterFlow_Posts\Plugin::instance();
