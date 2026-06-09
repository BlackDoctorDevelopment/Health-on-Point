<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HOP_Revalidate {
    public function __construct() {
        add_action( 'save_post_health_assessment', [ $this, 'ping' ], 20, 1 );
    }

    public function ping( int $post_id ): void {
        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) return;
        if ( get_post_status( $post_id ) !== 'publish' ) return;

        $secret = get_option( 'hop_vercel_revalidate_secret' );
        $base   = get_option( 'hop_vercel_url' );
        $slug   = get_post_field( 'post_name', $post_id );

        if ( ! $secret || ! $base || ! $slug ) return;

        $url = trailingslashit( $base ) . 'api/revalidate';

        wp_remote_post( $url, [
            'headers'  => [ 'Content-Type' => 'application/json' ],
            'body'     => wp_json_encode( [ 'secret' => $secret, 'slug' => $slug ] ),
            'timeout'  => 5,
            'blocking' => false,
        ] );
    }
}
