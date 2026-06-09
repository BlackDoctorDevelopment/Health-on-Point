<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

delete_option( 'hop_tavus_api_key' );
delete_option( 'hop_vercel_url' );
delete_option( 'hop_vercel_revalidate_secret' );
delete_option( 'hop_gam_network_code' );
delete_transient( 'hop_tavus_replicas' );
delete_transient( 'hop_tavus_personas' );
