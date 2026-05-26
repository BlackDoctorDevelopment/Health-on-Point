<?php
/**
 * Registers all MetaBox meta boxes for the health_assessment post type.
 *
 * Requires:
 *   - MetaBox (free core)            https://wordpress.org/plugins/meta-box/
 *   - MB Group (free extension)      https://metabox.io/plugins/meta-box-group/
 *
 * Optional but recommended:
 *   - MB Conditional Logic           https://metabox.io/plugins/meta-box-conditional-logic/
 *     (enables the 'visible' rules on fields — marked below with [CL])
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class HOP_Fields {
    public function __construct() {
        add_filter( 'rwmb_meta_boxes', [ $this, 'register' ] );
    }

    public function register( array $meta_boxes ): array {
        $pt = [ 'health_assessment' ];

        $meta_boxes[] = $this->basics( $pt );
        $meta_boxes[] = $this->branding( $pt );
        $meta_boxes[] = $this->landing( $pt );
        $meta_boxes[] = $this->terms( $pt );
        $meta_boxes[] = $this->presession( $pt );
        $meta_boxes[] = $this->tavus( $pt );
        $meta_boxes[] = $this->questions( $pt );
        $meta_boxes[] = $this->scoring( $pt );
        $meta_boxes[] = $this->monetization_loading( $pt );
        $meta_boxes[] = $this->monetization_results( $pt );
        $meta_boxes[] = $this->monetization_gam( $pt );
        $meta_boxes[] = $this->embedding( $pt );

        return $meta_boxes;
    }

    // ── 1. Basics ─────────────────────────────────────────────────────────────

    private function basics( array $pt ): array {
        return [
            'id'         => 'hop_basics',
            'title'      => __( 'Basics', 'health-on-point' ),
            'post_types' => $pt,
            'context'    => 'normal',
            'priority'   => 'high',
            'fields'     => [
                [
                    'id'          => 'instrument_label',
                    'type'        => 'text',
                    'name'        => __( 'Validated Instrument Label', 'health-on-point' ),
                    'desc'        => __( 'e.g. "Insomnia Severity Index (ISI)" or "Mayo UC Activity Index"', 'health-on-point' ),
                    'required'    => true,
                    'placeholder' => 'Insomnia Severity Index (ISI)',
                ],
                [
                    'id'       => 'question_count',
                    'type'     => 'number',
                    'name'     => __( 'Number of Questions', 'health-on-point' ),
                    'required' => true,
                    'min'      => 1,
                    'max'      => 50,
                    'std'      => 7,
                    'columns'  => 3,
                ],
                [
                    'id'          => 'estimated_duration',
                    'type'        => 'text',
                    'name'        => __( 'Estimated Duration', 'health-on-point' ),
                    'desc'        => __( 'Shown on the landing page, e.g. "5 min"', 'health-on-point' ),
                    'std'         => '5 min',
                    'placeholder' => '5 min',
                    'columns'     => 3,
                ],
            ],
        ];
    }

    // ── 2. Branding ───────────────────────────────────────────────────────────

    private function branding( array $pt ): array {
        return [
            'id'         => 'hop_branding',
            'title'      => __( 'Branding', 'health-on-point' ),
            'post_types' => $pt,
            'context'    => 'normal',
            'fields'     => [
                [
                    'id'           => 'partner_logo',
                    'type'         => 'single_image',
                    'name'         => __( 'Partner Logo', 'health-on-point' ),
                    'desc'         => __( 'Shown in the assessment header. PNG or SVG with transparent background recommended.', 'health-on-point' ),
                    'force_delete' => false,
                    'columns'      => 6,
                ],
                [
                    'id'           => 'hero_image',
                    'type'         => 'single_image',
                    'name'         => __( 'Hero Image', 'health-on-point' ),
                    'desc'         => __( 'Background or illustration for the landing page hero.', 'health-on-point' ),
                    'force_delete' => false,
                    'columns'      => 6,
                ],
                [
                    'id'      => 'primary_color',
                    'type'    => 'color',
                    'name'    => __( 'Primary Color', 'health-on-point' ),
                    'std'     => '#000000',
                    'columns' => 4,
                ],
                [
                    'id'      => 'accent_color',
                    'type'    => 'color',
                    'name'    => __( 'Accent / CTA Color', 'health-on-point' ),
                    'std'     => '#EFB14D',
                    'columns' => 4,
                ],
                [
                    'id'          => 'footer_copy',
                    'type'        => 'text',
                    'name'        => __( 'Footer Copyright Line', 'health-on-point' ),
                    'placeholder' => '© 2025 BlackDoctor.com — Health On Point',
                    'columns'     => 4,
                ],
                [
                    'id'          => 'disclaimer',
                    'type'        => 'textarea',
                    'name'        => __( 'Disclaimer Text', 'health-on-point' ),
                    'desc'        => __( 'Shown below the intake form.', 'health-on-point' ),
                    'rows'        => 3,
                    'placeholder' => 'For informational purposes only — not medical advice.',
                ],
            ],
        ];
    }

    // ── 3. Landing Page Copy ──────────────────────────────────────────────────

    private function landing( array $pt ): array {
        return [
            'id'         => 'hop_landing',
            'title'      => __( 'Landing Page Copy', 'health-on-point' ),
            'post_types' => $pt,
            'context'    => 'normal',
            'fields'     => [
                [
                    'id'          => 'hero_headline',
                    'type'        => 'text',
                    'name'        => __( 'Hero Headline', 'health-on-point' ),
                    'required'    => true,
                    'placeholder' => "Your Health Story Matters. Let's Talk.",
                ],
                [
                    'id'      => 'hero_subhead',
                    'type'    => 'wysiwyg',
                    'name'    => __( 'Hero Subheading', 'health-on-point' ),
                    'desc'    => __( 'Supporting copy below the headline. Bold and links supported.', 'health-on-point' ),
                    'options' => [ 'textarea_rows' => 5, 'media_buttons' => false ],
                ],
                [
                    'id'          => 'form_heading',
                    'type'        => 'text',
                    'name'        => __( 'Form Heading', 'health-on-point' ),
                    'placeholder' => 'Start Your Assessment',
                    'columns'     => 6,
                ],
                [
                    'id'      => 'cta_label',
                    'type'    => 'text',
                    'name'    => __( 'CTA Button Label', 'health-on-point' ),
                    'std'     => 'Start My Assessment →',
                    'columns' => 6,
                ],
                [
                    'id'          => 'topic_chips',
                    'type'        => 'group',
                    'name'        => __( 'Topic Chips', 'health-on-point' ),
                    'desc'        => __( 'Short labels shown as pills on the landing page (e.g. Sleep Onset, Sleep Maintenance).', 'health-on-point' ),
                    'clone'       => true,
                    'sort_clone'  => true,
                    'add_button'  => __( 'Add Chip', 'health-on-point' ),
                    'fields'      => [
                        [
                            'id'       => 'label',
                            'type'     => 'text',
                            'name'     => __( 'Label', 'health-on-point' ),
                            'required' => true,
                        ],
                    ],
                ],
                [
                    'id'          => 'value_props',
                    'type'        => 'group',
                    'name'        => __( 'Value Proposition Cards', 'health-on-point' ),
                    'desc'        => __( 'The benefit cards shown below the hero copy (typically 3).', 'health-on-point' ),
                    'clone'       => true,
                    'sort_clone'  => true,
                    'collapsible' => true,
                    'group_title' => '{title}',
                    'add_button'  => __( 'Add Card', 'health-on-point' ),
                    'fields'      => [
                        [
                            'id'          => 'icon',
                            'type'        => 'text',
                            'name'        => __( 'Icon (emoji)', 'health-on-point' ),
                            'placeholder' => '💬',
                            'columns'     => 3,
                        ],
                        [
                            'id'       => 'title',
                            'type'     => 'text',
                            'name'     => __( 'Title', 'health-on-point' ),
                            'required' => true,
                            'columns'  => 5,
                        ],
                        [
                            'id'      => 'description',
                            'type'    => 'textarea',
                            'name'    => __( 'Description', 'health-on-point' ),
                            'rows'    => 2,
                            'columns' => 4,
                        ],
                    ],
                ],
            ],
        ];
    }

    // ── 4. Terms & Conditions ─────────────────────────────────────────────────

    private function terms( array $pt ): array {
        return [
            'id'         => 'hop_terms',
            'title'      => __( 'Terms & Conditions', 'health-on-point' ),
            'post_types' => $pt,
            'context'    => 'normal',
            'fields'     => [
                [
                    'id'   => 'terms_enabled',
                    'type' => 'checkbox',
                    'name' => __( 'Enable Terms Gate', 'health-on-point' ),
                    'desc' => __( 'Show a T&C modal users must accept before starting the assessment.', 'health-on-point' ),
                    'std'  => 0,
                ],
                [
                    'id'          => 'terms_heading',
                    'type'        => 'text',
                    'name'        => __( 'Modal Heading', 'health-on-point' ),
                    'std'         => 'Terms & Conditions',
                    'visible'     => [ 'terms_enabled', '=', true ], // [CL] requires MB Conditional Logic
                ],
                [
                    'id'      => 'terms_body',
                    'type'    => 'wysiwyg',
                    'name'    => __( 'Body Copy', 'health-on-point' ),
                    'options' => [ 'textarea_rows' => 8, 'media_buttons' => false ],
                    'visible' => [ 'terms_enabled', '=', true ], // [CL]
                ],
                [
                    'id'      => 'terms_url',
                    'type'    => 'url',
                    'name'    => __( 'Full Terms URL', 'health-on-point' ),
                    'desc'    => __( 'Link to your complete Terms of Service page.', 'health-on-point' ),
                    'visible' => [ 'terms_enabled', '=', true ], // [CL]
                ],
                [
                    'id'      => 'terms_checkbox_label',
                    'type'    => 'text',
                    'name'    => __( 'Checkbox Label', 'health-on-point' ),
                    'std'     => 'I agree to the Terms & Conditions',
                    'columns' => 6,
                    'visible' => [ 'terms_enabled', '=', true ], // [CL]
                ],
                [
                    'id'      => 'terms_button_label',
                    'type'    => 'text',
                    'name'    => __( 'Accept Button Label', 'health-on-point' ),
                    'std'     => 'Continue',
                    'columns' => 6,
                    'visible' => [ 'terms_enabled', '=', true ], // [CL]
                ],
            ],
        ];
    }

    // ── 5. Pre-Session Screen ─────────────────────────────────────────────────

    private function presession( array $pt ): array {
        return [
            'id'         => 'hop_presession',
            'title'      => __( 'Pre-Session Screen', 'health-on-point' ),
            'post_types' => $pt,
            'context'    => 'normal',
            'fields'     => [
                [
                    'id'          => 'pre_pill_text',
                    'type'        => 'text',
                    'name'        => __( 'Pill / Badge Text', 'health-on-point' ),
                    'desc'        => __( 'Short label shown above the heading. e.g. "Insomnia Assessment — Ready to Begin"', 'health-on-point' ),
                    'placeholder' => 'Assessment — Ready to Begin',
                ],
                [
                    'id'          => 'pre_heading',
                    'type'        => 'text',
                    'name'        => __( 'Heading', 'health-on-point' ),
                    'required'    => true,
                    'placeholder' => 'Before We Start',
                ],
                [
                    'id'   => 'pre_intro',
                    'type' => 'textarea',
                    'name' => __( 'Intro Paragraph', 'health-on-point' ),
                    'desc' => __( 'Shown below the heading to set expectations.', 'health-on-point' ),
                    'rows' => 3,
                ],
                [
                    'id'          => 'pre_cards',
                    'type'        => 'group',
                    'name'        => __( 'Expectation Cards', 'health-on-point' ),
                    'desc'        => __( 'Cards explaining what to expect (e.g. Speak naturally / ~5 min / Private).', 'health-on-point' ),
                    'clone'       => true,
                    'sort_clone'  => true,
                    'collapsible' => true,
                    'group_title' => '{title}',
                    'add_button'  => __( 'Add Card', 'health-on-point' ),
                    'fields'      => [
                        [
                            'id'          => 'icon',
                            'type'        => 'text',
                            'name'        => __( 'Icon (emoji)', 'health-on-point' ),
                            'placeholder' => '🎙️',
                            'columns'     => 2,
                        ],
                        [
                            'id'       => 'title',
                            'type'     => 'text',
                            'name'     => __( 'Title', 'health-on-point' ),
                            'required' => true,
                            'columns'  => 4,
                        ],
                        [
                            'id'      => 'body',
                            'type'    => 'textarea',
                            'name'    => __( 'Body', 'health-on-point' ),
                            'rows'    => 2,
                            'columns' => 6,
                        ],
                    ],
                ],
                [
                    'id'   => 'pre_video_default',
                    'type' => 'checkbox',
                    'name' => __( 'Camera On By Default', 'health-on-point' ),
                    'desc' => __( 'Start the session with user camera enabled.', 'health-on-point' ),
                    'std'  => 1,
                ],
                [
                    'id'      => 'pre_languages',
                    'type'    => 'checkbox_list',
                    'name'    => __( 'Available Languages', 'health-on-point' ),
                    'options' => [
                        'english'      => 'English',
                        'spanish'      => 'Spanish',
                        'multilingual' => 'Multilingual (auto-detect)',
                    ],
                    'std'     => [ 'english' ],
                    'inline'  => true,
                ],
                [
                    'id'      => 'pre_join_label',
                    'type'    => 'text',
                    'name'    => __( 'Join Button Label', 'health-on-point' ),
                    'std'     => 'Start My Assessment',
                    'columns' => 6,
                ],
            ],
        ];
    }

    // ── 6. Tavus Configuration ────────────────────────────────────────────────

    private function tavus( array $pt ): array {
        return [
            'id'         => 'hop_tavus',
            'title'      => __( 'Tavus Configuration', 'health-on-point' ),
            'post_types' => $pt,
            'context'    => 'normal',
            'fields'     => [
                [
                    'id'          => 'tavus_replica_id',
                    'type'        => 'select',
                    'name'        => __( 'Tavus Replica', 'health-on-point' ),
                    'desc'        => __( 'Populated from the Tavus API. Refresh the page to update. Set API key in Settings first.', 'health-on-point' ),
                    'options'     => [ 'HOP_Tavus', 'replica_options' ],
                    'required'    => true,
                    'columns'     => 6,
                ],
                [
                    'id'      => 'tavus_persona_id',
                    'type'    => 'select',
                    'name'    => __( 'Tavus Persona', 'health-on-point' ),
                    'desc'    => __( 'Optional — leave blank to use the replica\'s default persona.', 'health-on-point' ),
                    'options' => [ 'HOP_Tavus', 'persona_options' ],
                    'columns' => 6,
                ],
                [
                    'id'   => 'tavus_system_prompt',
                    'type' => 'textarea',
                    'name' => __( 'Conversation Context / System Prompt', 'health-on-point' ),
                    'desc' => __( 'Instructions sent to the Tavus persona at session start. The question list is appended automatically.', 'health-on-point' ),
                    'rows' => 10,
                ],
                [
                    'id'          => 'tavus_greeting',
                    'type'        => 'text',
                    'name'        => __( 'Greeting Template', 'health-on-point' ),
                    'desc'        => __( 'Use {name} to personalize with the user\'s first name.', 'health-on-point' ),
                    'std'         => "Hi {name}, I'm your health advisor. We're going to walk through a short assessment together.",
                    'placeholder' => "Hi {name}, I'm your health advisor.",
                ],
                [
                    'id'      => 'tavus_timeout',
                    'type'    => 'number',
                    'name'    => __( 'Conversation Timeout (seconds)', 'health-on-point' ),
                    'std'     => 600,
                    'min'     => 60,
                    'max'     => 3600,
                    'columns' => 4,
                ],
            ],
        ];
    }

    // ── 7. Questions ──────────────────────────────────────────────────────────

    private function questions( array $pt ): array {
        return [
            'id'         => 'hop_questions',
            'title'      => __( 'Questions', 'health-on-point' ),
            'post_types' => $pt,
            'context'    => 'normal',
            'fields'     => [
                [
                    'id'          => 'questions',
                    'type'        => 'group',
                    'name'        => __( 'Assessment Questions', 'health-on-point' ),
                    'desc'        => __( 'Add each question the AI advisor will ask. Use the "Bulk Import Questions" sidebar panel to upload a CSV.', 'health-on-point' ),
                    'clone'       => true,
                    'sort_clone'  => true,
                    'collapsible' => true,
                    'group_title' => '{short_label}',
                    'add_button'  => __( 'Add Question', 'health-on-point' ),
                    'fields'      => [
                        [
                            'id'      => 'order',
                            'type'    => 'number',
                            'name'    => __( 'Order', 'health-on-point' ),
                            'std'     => 1,
                            'min'     => 1,
                            'columns' => 2,
                        ],
                        [
                            'id'          => 'short_label',
                            'type'        => 'text',
                            'name'        => __( 'Short Label', 'health-on-point' ),
                            'desc'        => __( 'e.g. "Sleep Onset"', 'health-on-point' ),
                            'required'    => true,
                            'columns'     => 4,
                        ],
                        [
                            'id'      => 'category',
                            'type'    => 'text',
                            'name'    => __( 'Category', 'health-on-point' ),
                            'columns' => 3,
                        ],
                        [
                            'id'      => 'answer_type',
                            'type'    => 'select',
                            'name'    => __( 'Answer Type', 'health-on-point' ),
                            'options' => [
                                'likert_0_4'      => 'Likert 0–4',
                                'multiple_choice' => 'Multiple Choice',
                                'free_text'       => 'Free Text',
                                'yes_no'          => 'Yes / No',
                            ],
                            'std'     => 'likert_0_4',
                            'columns' => 3,
                        ],
                        [
                            'id'       => 'prompt',
                            'type'     => 'textarea',
                            'name'     => __( 'Question Prompt (spoken by AI)', 'health-on-point' ),
                            'desc'     => __( 'Full text the AI advisor will speak. e.g. "How much difficulty do you have falling asleep?"', 'health-on-point' ),
                            'required' => true,
                            'rows'     => 3,
                        ],
                        [
                            'id'         => 'options',
                            'type'       => 'group',
                            'name'       => __( 'Answer Options', 'health-on-point' ),
                            'desc'       => __( 'Define selectable answers and their score weights.', 'health-on-point' ),
                            'clone'      => true,
                            'add_button' => __( 'Add Option', 'health-on-point' ),
                            'visible'    => [ 'answer_type', '!=', 'free_text' ], // [CL]
                            'fields'     => [
                                [
                                    'id'       => 'label',
                                    'type'     => 'text',
                                    'name'     => __( 'Label', 'health-on-point' ),
                                    'required' => true,
                                    'columns'  => 8,
                                ],
                                [
                                    'id'      => 'score',
                                    'type'    => 'number',
                                    'name'    => __( 'Score', 'health-on-point' ),
                                    'std'     => 0,
                                    'columns' => 4,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ── 8. Scoring & Results ──────────────────────────────────────────────────

    private function scoring( array $pt ): array {
        return [
            'id'         => 'hop_scoring',
            'title'      => __( 'Scoring & Results', 'health-on-point' ),
            'post_types' => $pt,
            'context'    => 'normal',
            'fields'     => [
                [
                    'id'          => 'scoring_tiers',
                    'type'        => 'group',
                    'name'        => __( 'Score Tiers', 'health-on-point' ),
                    'desc'        => __( 'Define score ranges and copy shown to users who land in each tier. Ranges should be contiguous and non-overlapping.', 'health-on-point' ),
                    'clone'       => true,
                    'sort_clone'  => true,
                    'collapsible' => true,
                    'group_title' => '{tier_label}',
                    'add_button'  => __( 'Add Score Tier', 'health-on-point' ),
                    'fields'      => [
                        [
                            'id'       => 'min_score',
                            'type'     => 'number',
                            'name'     => __( 'Min Score (inclusive)', 'health-on-point' ),
                            'required' => true,
                            'columns'  => 3,
                        ],
                        [
                            'id'       => 'max_score',
                            'type'     => 'number',
                            'name'     => __( 'Max Score (inclusive)', 'health-on-point' ),
                            'required' => true,
                            'columns'  => 3,
                        ],
                        [
                            'id'       => 'tier_label',
                            'type'     => 'text',
                            'name'     => __( 'Tier Label', 'health-on-point' ),
                            'desc'     => __( 'e.g. "No clinically significant insomnia"', 'health-on-point' ),
                            'required' => true,
                            'columns'  => 4,
                        ],
                        [
                            'id'      => 'color',
                            'type'    => 'color',
                            'name'    => __( 'Color', 'health-on-point' ),
                            'std'     => '#27ae60',
                            'columns' => 2,
                        ],
                        [
                            'id'       => 'headline',
                            'type'     => 'text',
                            'name'     => __( 'Results Headline', 'health-on-point' ),
                            'desc'     => __( 'Displayed as the main heading on the results screen for this tier.', 'health-on-point' ),
                            'required' => true,
                        ],
                        [
                            'id'      => 'recommendation',
                            'type'    => 'wysiwyg',
                            'name'    => __( 'Recommendations', 'health-on-point' ),
                            'desc'    => __( 'Personalized advice shown to users in this score tier.', 'health-on-point' ),
                            'options' => [ 'textarea_rows' => 6, 'media_buttons' => false ],
                        ],
                        [
                            'id'         => 'resources',
                            'type'       => 'group',
                            'name'       => __( 'Follow-Up Resources', 'health-on-point' ),
                            'clone'      => true,
                            'add_button' => __( 'Add Resource', 'health-on-point' ),
                            'fields'     => [
                                [
                                    'id'       => 'title',
                                    'type'     => 'text',
                                    'name'     => __( 'Title', 'health-on-point' ),
                                    'required' => true,
                                    'columns'  => 6,
                                ],
                                [
                                    'id'       => 'url',
                                    'type'     => 'url',
                                    'name'     => __( 'URL', 'health-on-point' ),
                                    'required' => true,
                                    'columns'  => 6,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ── 9a. Monetization — Loading Screen Ad ──────────────────────────────────

    private function monetization_loading( array $pt ): array {
        return [
            'id'         => 'hop_monetization_loading',
            'title'      => __( 'Loading Screen Ad', 'health-on-point' ),
            'post_types' => $pt,
            'context'    => 'normal',
            'fields'     => [
                [
                    'id'      => 'loading_ad_mode',
                    'type'    => 'radio',
                    'name'    => __( 'Ad Mode', 'health-on-point' ),
                    'options' => [
                        'off'   => __( 'Off — show no ad', 'health-on-point' ),
                        'house' => __( 'House creative (upload image below)', 'health-on-point' ),
                        'gam'   => __( 'Google Ad Manager tag', 'health-on-point' ),
                    ],
                    'std'    => 'house',
                    'inline' => false,
                ],
                [
                    'id'           => 'loading_ad_house_image',
                    'type'         => 'single_image',
                    'name'         => __( 'House Creative Image', 'health-on-point' ),
                    'force_delete' => false,
                    'visible'      => [ 'loading_ad_mode', '=', 'house' ], // [CL]
                ],
                [
                    'id'      => 'loading_ad_house_url',
                    'type'    => 'url',
                    'name'    => __( 'House Creative Click-through URL', 'health-on-point' ),
                    'visible' => [ 'loading_ad_mode', '=', 'house' ], // [CL]
                ],
            ],
        ];
    }

    // ── 9b. Monetization — Results Screen Ad ─────────────────────────────────

    private function monetization_results( array $pt ): array {
        return [
            'id'         => 'hop_monetization_results',
            'title'      => __( 'Results Screen Ad', 'health-on-point' ),
            'post_types' => $pt,
            'context'    => 'normal',
            'fields'     => [
                [
                    'id'      => 'results_ad_mode',
                    'type'    => 'radio',
                    'name'    => __( 'Ad Mode', 'health-on-point' ),
                    'options' => [
                        'off'   => __( 'Off — show no ad', 'health-on-point' ),
                        'house' => __( 'House creative', 'health-on-point' ),
                        'gam'   => __( 'Google Ad Manager tag', 'health-on-point' ),
                    ],
                    'std'    => 'off',
                    'inline' => false,
                ],
                [
                    'id'           => 'results_ad_house_image',
                    'type'         => 'single_image',
                    'name'         => __( 'House Creative Image', 'health-on-point' ),
                    'force_delete' => false,
                    'visible'      => [ 'results_ad_mode', '=', 'house' ], // [CL]
                ],
                [
                    'id'      => 'results_ad_house_url',
                    'type'    => 'url',
                    'name'    => __( 'House Creative Click-through URL', 'health-on-point' ),
                    'visible' => [ 'results_ad_mode', '=', 'house' ], // [CL]
                ],
                [
                    'id'      => 'results_ad_position',
                    'type'    => 'select',
                    'name'    => __( 'Position on Results Page', 'health-on-point' ),
                    'options' => [
                        'above'   => __( 'Above score', 'health-on-point' ),
                        'below'   => __( 'Below recommendations', 'health-on-point' ),
                        'sidebar' => __( 'Sidebar', 'health-on-point' ),
                    ],
                    'std'     => 'below',
                ],
            ],
        ];
    }

    // ── 9c. Monetization — Google Ad Manager ─────────────────────────────────

    private function monetization_gam( array $pt ): array {
        return [
            'id'         => 'hop_monetization_gam',
            'title'      => __( 'Google Ad Manager (GAM)', 'health-on-point' ),
            'post_types' => $pt,
            'context'    => 'normal',
            'fields'     => [
                [
                    'id'          => 'gam_ad_unit_path',
                    'type'        => 'text',
                    'name'        => __( 'GAM Ad Unit Path', 'health-on-point' ),
                    'desc'        => __( 'e.g. /blackdoctor/health-on-point/insomnia — the network code from Settings is prepended automatically.', 'health-on-point' ),
                    'placeholder' => '/blackdoctor/health-on-point/slug',
                ],
                [
                    'id'          => 'gam_sizes',
                    'type'        => 'text',
                    'name'        => __( 'Ad Sizes (JSON array)', 'health-on-point' ),
                    'desc'        => __( 'JSON array of [width,height] pairs. e.g. [[300,250],[728,90]]', 'health-on-point' ),
                    'std'         => '[[300,250],[728,90]]',
                    'placeholder' => '[[300,250],[728,90]]',
                ],
                [
                    'id'         => 'gam_targeting',
                    'type'       => 'group',
                    'name'       => __( 'Targeting Key-Values', 'health-on-point' ),
                    'desc'       => __( 'Custom key-value pairs passed to GAM for reporting (e.g. assessment=insomnia).', 'health-on-point' ),
                    'clone'      => true,
                    'add_button' => __( 'Add Key-Value', 'health-on-point' ),
                    'fields'     => [
                        [
                            'id'       => 'key',
                            'type'     => 'text',
                            'name'     => __( 'Key', 'health-on-point' ),
                            'required' => true,
                            'columns'  => 6,
                        ],
                        [
                            'id'       => 'value',
                            'type'     => 'text',
                            'name'     => __( 'Value', 'health-on-point' ),
                            'required' => true,
                            'columns'  => 6,
                        ],
                    ],
                ],
            ],
        ];
    }

    // ── 10. Embedding ─────────────────────────────────────────────────────────

    private function embedding( array $pt ): array {
        return [
            'id'         => 'hop_embedding',
            'title'      => __( 'Embedding', 'health-on-point' ),
            'post_types' => $pt,
            'context'    => 'normal',
            'fields'     => [
                [
                    'id'   => 'emb_instructions',
                    'type' => 'custom_html',
                    'std'  => '
                        <h4 style="margin-top:0">WordPress Shortcode</h4>
                        <p>Copy and paste into any post or page — replace <strong>YOUR-SLUG</strong> with this assessment\'s slug (shown in the Assessments list):</p>
                        <pre style="background:#f0f0f1;padding:10px;border-radius:4px;font-size:13px">[health-on-point id="YOUR-SLUG"]</pre>
                        <p>Add <code>height="900"</code> to adjust the iframe height (default 800px).</p>
                        <h4>Direct Iframe</h4>
                        <pre style="background:#f0f0f1;padding:10px;border-radius:4px;font-size:13px">&lt;iframe src="https://YOUR-VERCEL-URL/YOUR-SLUG" width="100%" height="800" frameborder="0" sandbox="allow-scripts allow-same-origin allow-forms allow-popups"&gt;&lt;/iframe&gt;</pre>
                        <h4>REST API (consumed by the Next.js app)</h4>
                        <pre style="background:#f0f0f1;padding:10px;border-radius:4px;font-size:13px">GET /wp-json/hop/v1/assessment/YOUR-SLUG</pre>
                        <p style="margin-bottom:0">Configure the Vercel URL under <strong>Assessments → Settings</strong>.</p>
                    ',
                ],
            ],
        ];
    }
}
