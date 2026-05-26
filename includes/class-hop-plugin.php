<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HOP_Plugin {
    private static $instance;

    public static function instance(): self {
        if ( ! self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        foreach ( [
            'class-hop-cpt',
            'class-hop-tavus',    // load before class-hop-fields so static methods are available
            'class-hop-fields',
            'class-hop-rest',
            'class-hop-shortcode',
            'class-hop-import',
            'class-hop-revalidate',
            'class-hop-admin',
        ] as $file ) {
            require_once HOP_PATH . "includes/{$file}.php";
        }

        new HOP_CPT();
        new HOP_Tavus();
        new HOP_Fields();
        new HOP_REST();
        new HOP_Shortcode();
        new HOP_Import();
        new HOP_Revalidate();
        new HOP_Admin();

        add_action( 'admin_menu', [ $this, 'settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function settings_page(): void {
        add_submenu_page(
            'edit.php?post_type=health_assessment',
            __( 'HOP Settings', 'health-on-point' ),
            __( 'Settings', 'health-on-point' ),
            'manage_options',
            'hop-settings',
            [ $this, 'render_settings' ]
        );
    }

    public function register_settings(): void {
        foreach ( [ 'hop_tavus_api_key', 'hop_vercel_url', 'hop_vercel_revalidate_secret', 'hop_gam_network_code' ] as $opt ) {
            register_setting( 'hop_settings', $opt );
        }
    }

    public function render_settings(): void {
        include HOP_PATH . 'admin/views/settings.php';
    }
}
