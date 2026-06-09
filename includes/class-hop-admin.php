<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HOP_Admin {
    public function __construct() {
        add_filter( 'post_row_actions', [ $this, 'duplicate_action' ], 10, 2 );
        add_action( 'admin_action_hop_duplicate', [ $this, 'handle_duplicate' ] );
        add_filter( 'manage_health_assessment_posts_columns', [ $this, 'columns' ] );
        add_action( 'manage_health_assessment_posts_custom_column', [ $this, 'column_content' ], 10, 2 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
        add_action( 'admin_notices', [ $this, 'import_notice' ] );
    }

    public function enqueue( string $hook ): void {
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'health_assessment' ) return;
        wp_enqueue_style( 'hop-admin', HOP_URL . 'admin/css/admin.css', [], HOP_VERSION );
        wp_enqueue_script( 'hop-admin', HOP_URL . 'admin/js/admin.js', [ 'jquery' ], HOP_VERSION, true );
    }

    public function import_notice(): void {
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'health_assessment' ) return;

        $status = sanitize_key( $_GET['hop_import'] ?? '' );
        if ( $status === 'success' ) {
            $count = (int) ( $_GET['count'] ?? 0 );
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                esc_html( sprintf( _n( '%d question imported.', '%d questions imported.', $count, 'health-on-point' ), $count ) )
            );
        } elseif ( $status === 'no_file' ) {
            echo '<div class="notice notice-error is-dismissible"><p>' .
                 esc_html__( 'No CSV file was uploaded.', 'health-on-point' ) . '</p></div>';
        }
    }

    public function columns( array $columns ): array {
        $new = [];
        foreach ( $columns as $k => $v ) {
            $new[ $k ] = $v;
            if ( $k === 'title' ) {
                $new['hop_slug']    = __( 'Slug', 'health-on-point' );
                $new['hop_replica'] = __( 'Tavus Replica', 'health-on-point' );
                $new['hop_embed']   = __( 'Shortcode', 'health-on-point' );
            }
        }
        return $new;
    }

    public function column_content( string $column, int $post_id ): void {
        $slug = get_post_field( 'post_name', $post_id );
        if ( $column === 'hop_slug' ) {
            echo '<code>' . esc_html( $slug ) . '</code>';
        } elseif ( $column === 'hop_replica' ) {
            $replica = rwmb_meta( 'tavus_replica_id', [], $post_id );
            echo esc_html( $replica ?: '—' );
        } elseif ( $column === 'hop_embed' ) {
            echo '<code>[health-on-point id="' . esc_attr( $slug ) . '"]</code>';
        }
    }

    public function duplicate_action( array $actions, WP_Post $post ): array {
        if ( $post->post_type !== 'health_assessment' ) return $actions;
        $url = wp_nonce_url(
            admin_url( 'admin.php?action=hop_duplicate&post=' . $post->ID ),
            'hop_duplicate_' . $post->ID
        );
        $actions['hop_duplicate'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Duplicate', 'health-on-point' ) . '</a>';
        return $actions;
    }

    public function handle_duplicate(): void {
        $post_id = (int) ( $_GET['post'] ?? 0 );
        check_admin_referer( 'hop_duplicate_' . $post_id );

        if ( ! current_user_can( 'edit_posts' ) || ! $post_id ) {
            wp_die( esc_html__( 'Unauthorized.', 'health-on-point' ) );
        }

        $post = get_post( $post_id );
        if ( ! $post ) wp_die( esc_html__( 'Assessment not found.', 'health-on-point' ) );

        $new_id = wp_insert_post( [
            'post_title'  => $post->post_title . ' (Copy)',
            'post_type'   => 'health_assessment',
            'post_status' => 'draft',
            'post_author' => get_current_user_id(),
        ] );

        if ( is_wp_error( $new_id ) ) wp_die( esc_html( $new_id->get_error_message() ) );

        foreach ( get_post_meta( $post_id ) as $key => $values ) {
            foreach ( $values as $value ) {
                add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
            }
        }

        wp_safe_redirect( get_edit_post_link( $new_id, 'url' ) );
        exit;
    }
}
