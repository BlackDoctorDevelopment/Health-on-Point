<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HOP_REST {
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'routes' ] );
    }

    public function routes(): void {
        register_rest_route( 'hop/v1', '/assessment/(?P<slug>[a-z0-9-]+)', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_assessment' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'slug' => [ 'sanitize_callback' => 'sanitize_title' ],
            ],
        ] );

        register_rest_route( 'hop/v1', '/assessments', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'list_assessments' ],
            'permission_callback' => '__return_true',
        ] );
    }

    public function get_assessment( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $posts = get_posts( [
            'post_type'   => 'health_assessment',
            'name'        => $request['slug'],
            'post_status' => 'publish',
            'numberposts' => 1,
        ] );

        if ( empty( $posts ) ) {
            return new WP_Error( 'not_found', 'Assessment not found', [ 'status' => 404 ] );
        }

        return rest_ensure_response( $this->serialize( $posts[0] ) );
    }

    public function list_assessments(): WP_REST_Response {
        $posts = get_posts( [
            'post_type'   => 'health_assessment',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC',
        ] );
        return rest_ensure_response( array_map( [ $this, 'serialize' ], $posts ) );
    }

    private function meta( string $key, int $id ): mixed {
        return rwmb_meta( $key, [], $id );
    }

    private function img( string $key, int $id ): ?string {
        $img_id = (int) $this->meta( $key, $id );
        return $img_id ? ( wp_get_attachment_image_url( $img_id, 'full' ) ?: null ) : null;
    }

    private function serialize( WP_Post $post ): array {
        $id = $post->ID;

        return [
            'id'    => $id,
            'slug'  => $post->post_name,
            'title' => $post->post_title,

            'basics' => [
                'instrument_label'   => $this->meta( 'instrument_label', $id ),
                'question_count'     => (int) $this->meta( 'question_count', $id ),
                'estimated_duration' => $this->meta( 'estimated_duration', $id ),
            ],

            'branding' => [
                'logo'          => $this->img( 'partner_logo', $id ),
                'hero_image'    => $this->img( 'hero_image', $id ),
                'primary_color' => $this->meta( 'primary_color', $id ),
                'accent_color'  => $this->meta( 'accent_color', $id ),
                'footer_copy'   => $this->meta( 'footer_copy', $id ),
                'disclaimer'    => $this->meta( 'disclaimer', $id ),
            ],

            'landing' => [
                'hero_headline' => $this->meta( 'hero_headline', $id ),
                'hero_subhead'  => $this->meta( 'hero_subhead', $id ),
                'form_heading'  => $this->meta( 'form_heading', $id ),
                'cta_label'     => $this->meta( 'cta_label', $id ),
                'topic_chips'   => $this->meta( 'topic_chips', $id ) ?: [],
                'value_props'   => $this->meta( 'value_props', $id ) ?: [],
            ],

            'terms' => [
                'enabled'        => (bool) $this->meta( 'terms_enabled', $id ),
                'heading'        => $this->meta( 'terms_heading', $id ),
                'body'           => $this->meta( 'terms_body', $id ),
                'full_tos_url'   => $this->meta( 'terms_url', $id ),
                'checkbox_label' => $this->meta( 'terms_checkbox_label', $id ),
                'button_label'   => $this->meta( 'terms_button_label', $id ),
            ],

            'presession' => [
                'pill_text'         => $this->meta( 'pre_pill_text', $id ),
                'heading'           => $this->meta( 'pre_heading', $id ),
                'intro'             => $this->meta( 'pre_intro', $id ),
                'expectation_cards' => $this->meta( 'pre_cards', $id ) ?: [],
                'video_default_on'  => (bool) $this->meta( 'pre_video_default', $id ),
                'languages'         => $this->meta( 'pre_languages', $id ) ?: [ 'english' ],
                'join_button_label' => $this->meta( 'pre_join_label', $id ),
            ],

            'tavus' => [
                'replica_id'        => $this->meta( 'tavus_replica_id', $id ),
                'persona_id'        => $this->meta( 'tavus_persona_id', $id ),
                'system_prompt'     => $this->meta( 'tavus_system_prompt', $id ),
                'greeting_template' => $this->meta( 'tavus_greeting', $id ),
                'timeout_seconds'   => (int) $this->meta( 'tavus_timeout', $id ),
            ],

            'questions' => array_map( static function ( array $q ): array {
                return [
                    'order'       => (int) $q['order'],
                    'short_label' => $q['short_label'],
                    'prompt'      => $q['prompt'],
                    'answer_type' => $q['answer_type'],
                    'category'    => $q['category'],
                    'options'     => array_map( static fn( $o ) => [
                        'label' => $o['label'],
                        'score' => (int) $o['score'],
                    ], $q['options'] ?: [] ),
                ];
            }, $this->meta( 'questions', $id ) ?: [] ),

            'scoring' => array_map( static function ( array $t ): array {
                return [
                    'min'            => (int) $t['min_score'],
                    'max'            => (int) $t['max_score'],
                    'tier_label'     => $t['tier_label'],
                    'color'          => $t['color'],
                    'headline'       => $t['headline'],
                    'recommendation' => $t['recommendation'],
                    'resources'      => $t['resources'] ?: [],
                ];
            }, $this->meta( 'scoring_tiers', $id ) ?: [] ),

            'monetization' => [
                'loading_ad' => [
                    'mode'        => $this->meta( 'loading_ad_mode', $id ) ?: 'off',
                    'house_image' => $this->img( 'loading_ad_house_image', $id ),
                    'house_url'   => $this->meta( 'loading_ad_house_url', $id ),
                ],
                'results_ad' => [
                    'mode'        => $this->meta( 'results_ad_mode', $id ) ?: 'off',
                    'house_image' => $this->img( 'results_ad_house_image', $id ),
                    'house_url'   => $this->meta( 'results_ad_house_url', $id ),
                    'position'    => $this->meta( 'results_ad_position', $id ) ?: 'below',
                ],
                'gam' => [
                    'network_code' => get_option( 'hop_gam_network_code' ),
                    'ad_unit_path' => $this->meta( 'gam_ad_unit_path', $id ),
                    'sizes'        => $this->meta( 'gam_sizes', $id ),
                    'targeting'    => $this->meta( 'gam_targeting', $id ) ?: [],
                ],
            ],
        ];
    }
}
