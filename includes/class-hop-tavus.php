<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HOP_Tavus {
    public function __construct() {
        // Clear transient cache when API key is saved
        add_action( 'update_option_hop_tavus_api_key', [ $this, 'clear_cache' ] );
    }

    public static function replica_options(): array {
        return self::fetch( 'replicas', 'replica_id', 'replica_name' );
    }

    public static function persona_options(): array {
        return self::fetch( 'personas', 'persona_id', 'persona_name' );
    }

    private static function fetch( string $endpoint, string $id_key, string $name_key ): array {
        $cache_key = 'hop_tavus_' . $endpoint;
        $cached    = get_transient( $cache_key );
        if ( $cached !== false ) return $cached;

        $api_key = get_option( 'hop_tavus_api_key' );
        if ( ! $api_key ) {
            return [ '' => __( '— Enter Tavus API key in Settings first —', 'health-on-point' ) ];
        }

        $res = wp_remote_get( "https://tavusapi.com/v2/{$endpoint}", [
            'headers' => [ 'x-api-key' => $api_key ],
            'timeout' => 10,
        ] );

        if ( is_wp_error( $res ) ) {
            return [ '' => __( '— Could not reach Tavus API —', 'health-on-point' ) ];
        }

        $body = json_decode( wp_remote_retrieve_body( $res ), true );
        $out  = [ '' => __( '— Select —', 'health-on-point' ) ];

        foreach ( ( $body['data'] ?? [] ) as $item ) {
            if ( isset( $item[ $id_key ] ) ) {
                $out[ $item[ $id_key ] ] = $item[ $name_key ] ?? $item[ $id_key ];
            }
        }

        set_transient( $cache_key, $out, 5 * MINUTE_IN_SECONDS );
        return $out;
    }

    public function clear_cache(): void {
        delete_transient( 'hop_tavus_replicas' );
        delete_transient( 'hop_tavus_personas' );
    }
}
