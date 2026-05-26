<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1><?php esc_html_e( 'Health on Point — Settings', 'health-on-point' ); ?></h1>
    <form method="post" action="options.php">
        <?php settings_fields( 'hop_settings' ); ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="hop_tavus_api_key"><?php esc_html_e( 'Tavus API Key', 'health-on-point' ); ?></label>
                </th>
                <td>
                    <input name="hop_tavus_api_key" type="password" id="hop_tavus_api_key"
                           value="<?php echo esc_attr( get_option( 'hop_tavus_api_key' ) ); ?>"
                           class="regular-text" autocomplete="off">
                    <p class="description">
                        <?php esc_html_e( 'Found in your Tavus dashboard. Used to populate replica and persona dropdowns.', 'health-on-point' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="hop_vercel_url"><?php esc_html_e( 'Vercel App URL', 'health-on-point' ); ?></label>
                </th>
                <td>
                    <input name="hop_vercel_url" type="url" id="hop_vercel_url"
                           value="<?php echo esc_url( get_option( 'hop_vercel_url' ) ); ?>"
                           class="regular-text" placeholder="https://your-app.vercel.app">
                    <p class="description">
                        <?php esc_html_e( 'Base URL of the Next.js app on Vercel (no trailing slash needed).', 'health-on-point' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="hop_vercel_revalidate_secret"><?php esc_html_e( 'Vercel Revalidate Secret', 'health-on-point' ); ?></label>
                </th>
                <td>
                    <input name="hop_vercel_revalidate_secret" type="password" id="hop_vercel_revalidate_secret"
                           value="<?php echo esc_attr( get_option( 'hop_vercel_revalidate_secret' ) ); ?>"
                           class="regular-text" autocomplete="off">
                    <p class="description">
                        <?php esc_html_e( 'Must match REVALIDATE_SECRET in your Vercel environment variables. Triggered on every Assessment save.', 'health-on-point' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="hop_gam_network_code"><?php esc_html_e( 'GAM Network Code', 'health-on-point' ); ?></label>
                </th>
                <td>
                    <input name="hop_gam_network_code" type="text" id="hop_gam_network_code"
                           value="<?php echo esc_attr( get_option( 'hop_gam_network_code' ) ); ?>"
                           class="regular-text" placeholder="12345678">
                    <p class="description">
                        <?php esc_html_e( 'Your Google Ad Manager network code (numbers only). Applies to all assessments.', 'health-on-point' ); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
