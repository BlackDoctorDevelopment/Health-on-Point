<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HOP_CPT {
    public function __construct() {
        add_action( 'init', [ $this, 'register' ] );
    }

    public function register(): void {
        register_post_type( 'health_assessment', [
            'label'         => __( 'Assessments', 'health-on-point' ),
            'labels'        => [
                'name'               => __( 'Assessments', 'health-on-point' ),
                'singular_name'      => __( 'Assessment', 'health-on-point' ),
                'add_new_item'       => __( 'Add New Assessment', 'health-on-point' ),
                'edit_item'          => __( 'Edit Assessment', 'health-on-point' ),
                'new_item'           => __( 'New Assessment', 'health-on-point' ),
                'view_item'          => __( 'View Assessment', 'health-on-point' ),
                'search_items'       => __( 'Search Assessments', 'health-on-point' ),
                'not_found'          => __( 'No assessments found.', 'health-on-point' ),
                'not_found_in_trash' => __( 'No assessments found in Trash.', 'health-on-point' ),
            ],
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'show_in_rest'    => true,
            'menu_icon'       => 'dashicons-heart',
            'menu_position'   => 20,
            'supports'        => [ 'title', 'revisions', 'author' ],
            'rewrite'         => false,
            'has_archive'     => false,
            'capability_type' => 'post',
        ] );
    }
}
