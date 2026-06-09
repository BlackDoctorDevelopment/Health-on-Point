<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HOP_Shortcode {
    public function __construct() {
        add_shortcode( 'health-on-point', [ $this, 'render' ] );
    }

    public function render( array $atts ): string {
        $atts = shortcode_atts( [
            'id'     => '',
            'height' => '800',
            'title'  => 'Health on Point Assessment',
        ], $atts );

        if ( ! $atts['id'] ) return '';

        $base = trailingslashit( get_option( 'hop_vercel_url' ) );
        if ( ! $base || $base === '/' ) return '<!-- Health on Point: Vercel URL not configured -->';

        $slug = sanitize_title( $atts['id'] );
        $url  = esc_url( $base . $slug );
        $h    = (int) $atts['height'];
        $t    = esc_attr( $atts['title'] );

        return sprintf(
            '<iframe src="%s" title="%s" width="100%%" height="%d" frameborder="0" sandbox="allow-scripts allow-same-origin allow-forms allow-popups" loading="lazy" style="border:none;display:block;"></iframe>',
            $url, $t, $h
        );
    }
}
