<?php
/**
 * Plugin Name:       Health on Point
 * Plugin URI:        https://blackdoctor.com
 * Description:       Manage Health on Point AI-driven health assessments. Configure Tavus replicas, questions, scoring tiers, and ad placements without code.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            BlackDoctor.com
 * License:           GPL-2.0-or-later
 * Text Domain:       health-on-point
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'HOP_VERSION', '1.0.0' );
define( 'HOP_PATH', plugin_dir_path( __FILE__ ) );
define( 'HOP_URL',  plugin_dir_url( __FILE__ ) );

add_action( 'admin_init', function () {
    if ( ! class_exists( 'RWMB_Loader' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>' .
                 esc_html__( 'Health on Point requires the MetaBox plugin. Please install and activate it from metabox.io or WordPress.org.', 'health-on-point' ) .
                 '</p></div>';
        } );
    }
} );

require_once HOP_PATH . 'includes/class-hop-plugin.php';
add_action( 'plugins_loaded', [ 'HOP_Plugin', 'instance' ] );
