<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HOP_Import {
    public function __construct() {
        add_action( 'add_meta_boxes_health_assessment', [ $this, 'meta_box' ] );
        add_action( 'admin_post_hop_import_questions', [ $this, 'handle_upload' ] );
    }

    public function meta_box(): void {
        add_meta_box(
            'hop_csv_import',
            __( 'Bulk Import Questions', 'health-on-point' ),
            [ $this, 'render' ],
            'health_assessment',
            'side'
        );
    }

    public function render( WP_Post $post ): void {
        ?>
        <p class="description">
            <?php esc_html_e( 'CSV columns (include a header row):', 'health-on-point' ); ?><br>
            <code>order, short_label, category, answer_type, prompt, options_json</code>
        </p>
        <p class="description" style="margin-top:6px;">
            <?php esc_html_e( 'options_json example:', 'health-on-point' ); ?><br>
            <code>[{"label":"Not at all","score":0},{"label":"A little","score":1}]</code>
        </p>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="margin-top:10px;">
            <?php wp_nonce_field( 'hop_import_' . $post->ID, 'hop_import_nonce' ); ?>
            <input type="hidden" name="action" value="hop_import_questions">
            <input type="hidden" name="post_id" value="<?php echo esc_attr( $post->ID ); ?>">
            <input type="file" name="hop_csv" accept=".csv" style="width:100%;margin-bottom:8px;">
            <?php submit_button( __( 'Import Questions', 'health-on-point' ), 'secondary', 'submit', false ); ?>
        </form>
        <p class="description" style="margin-top:8px;color:#c0392b;">
            <?php esc_html_e( 'Existing questions will be replaced.', 'health-on-point' ); ?>
        </p>
        <?php
    }

    public function handle_upload(): void {
        $post_id = (int) ( $_POST['post_id'] ?? 0 );
        check_admin_referer( 'hop_import_' . $post_id, 'hop_import_nonce' );

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            wp_die( esc_html__( 'You do not have permission to edit this assessment.', 'health-on-point' ) );
        }

        $redirect = get_edit_post_link( $post_id, 'url' );

        if ( empty( $_FILES['hop_csv']['tmp_name'] ) ) {
            wp_safe_redirect( add_query_arg( 'hop_import', 'no_file', $redirect ) );
            exit;
        }

        $rows   = [];
        $handle = fopen( $_FILES['hop_csv']['tmp_name'], 'r' );

        if ( $handle !== false ) {
            fgetcsv( $handle ); // skip header row
            while ( ( $data = fgetcsv( $handle ) ) !== false ) {
                if ( count( $data ) < 6 ) continue;
                [ $order, $short_label, $category, $answer_type, $prompt, $options_json ] = $data;

                $options = [];
                $decoded = json_decode( $options_json, true );
                if ( is_array( $decoded ) ) {
                    foreach ( $decoded as $opt ) {
                        $options[] = [
                            'label' => sanitize_text_field( $opt['label'] ?? '' ),
                            'score' => (int) ( $opt['score'] ?? 0 ),
                        ];
                    }
                }

                $rows[] = [
                    'order'       => (int) $order,
                    'short_label' => sanitize_text_field( $short_label ),
                    'category'    => sanitize_text_field( $category ),
                    'answer_type' => sanitize_key( $answer_type ),
                    'prompt'      => sanitize_textarea_field( $prompt ),
                    'options'     => $options,
                ];
            }
            fclose( $handle );
        }

        if ( ! empty( $rows ) ) {
            // MetaBox group fields store cloned data as a serialized array in post meta
            update_post_meta( $post_id, 'questions', $rows );
        }

        wp_safe_redirect( add_query_arg( [ 'hop_import' => 'success', 'count' => count( $rows ) ], $redirect ) );
        exit;
    }
}
