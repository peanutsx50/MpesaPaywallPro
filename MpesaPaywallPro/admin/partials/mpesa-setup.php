<?php

/**
 * M-Pesa Setup Settings Template
 *
 * @package    MpesaPaywallPro
 * @subpackage MpesaPaywallPro/admin/partials
 * @link       https://festuswp.gumroad.com/l/MpesaPaywallPro
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Fetch the entire options array once
$options = get_option('mpesapaywallpro_options', []);
?>

<div class="mpesapaywallpro-settings-section">
    <h2 class="title">
        <span class="dashicons dashicons-businessperson"></span>
        <?php esc_html_e('M-Pesa API Configuration', 'mpesapaywallpro'); ?>
    </h2>

    <div class="mpesapaywallpro-notice notice-info">
        <p>
            <?php esc_html_e('Enter your M-Pesa Daraja API credentials. These are required to process payments through Safaricom.', 'mpesapaywallpro'); ?>
            <a href="https://developer.safaricom.co.ke/apis" target="_blank"><?php esc_html_e('Get API credentials', 'mpesapaywallpro'); ?></a>
        </p>
    </div>

    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="mpp_consumer_key"><?php esc_html_e('Consumer Key', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="password"
                    id="mpp_consumer_key"
                    name="mpesapaywallpro_options[mpp_consumer_key]"
                    value="<?php echo esc_attr($options['mpp_consumer_key'] ?? ''); ?>"
                    class="regular-text"
                    placeholder="Enter your M-Pesa Consumer Key">
                <p class="description">
                    <?php esc_html_e('Your M-Pesa Daraja API Consumer Key', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mpp_consumer_secret"><?php esc_html_e('Consumer Secret', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="password"
                    id="mpp_consumer_secret"
                    name="mpesapaywallpro_options[mpp_consumer_secret]"
                    value="<?php echo esc_attr($options['mpp_consumer_secret'] ?? ''); ?>"
                    class="regular-text"
                    placeholder="Enter your M-Pesa Consumer Secret">
                <p class="description">
                    <?php esc_html_e('Your M-Pesa Daraja API Consumer Secret', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mpp_shortcode"><?php esc_html_e('Business Shortcode', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="text"
                    id="mpp_shortcode"
                    name="mpesapaywallpro_options[mpp_shortcode]"
                    value="<?php echo esc_attr($options['mpp_shortcode'] ?? ''); ?>"
                    class="regular-text"
                    placeholder="e.g., 174379">
                <p class="description">
                    <?php esc_html_e('Your M-Pesa Business Shortcode (PayBill or Till Number)', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mpp_passkey"><?php esc_html_e('Passkey', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="password"
                    id="mpp_passkey"
                    name="mpesapaywallpro_options[mpp_passkey]"
                    value="<?php echo esc_attr($options['mpp_passkey'] ?? ''); ?>"
                    class="regular-text"
                    placeholder="Enter your M-Pesa Passkey">
                <p class="description">
                    <?php esc_html_e('Your M-Pesa Daraja API Passkey', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mpp_env"><?php esc_html_e('Environment', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <select id="mpp_env" name="mpesapaywallpro_options[mpp_env]" class="regular-text">
                    <option value="sandbox" <?php selected($options['mpp_env'] ?? 'sandbox', 'sandbox'); ?>>
                        <?php esc_html_e('Sandbox (Testing)', 'mpesapaywallpro'); ?>
                    </option>
                    <option value="production" <?php selected($options['mpp_env'] ?? '', 'production'); ?>>
                        <?php esc_html_e('Production (Live)', 'mpesapaywallpro'); ?>
                    </option>
                </select>
                <p class="description">
                    <?php esc_html_e('Select Sandbox for testing, Production for live transactions', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>
    </table>

    <div class="mpesapaywallpro-test-connection">
        <h3>
            <span class="dashicons dashicons-admin-plugins"></span>
            <?php esc_html_e('Test Connection', 'mpesapaywallpro'); ?>
        </h3>
        <p><?php esc_html_e('Test if your M-Pesa API credentials are working correctly:', 'mpesapaywallpro'); ?></p>
        <button type="button" id="test-mpesa-connection" class="button button-secondary">
            <?php esc_html_e('Test API Connection', 'mpesapaywallpro'); ?>
        </button>
        <div id="test-connection-result" class="test-result" style="display: none;"></div>
    </div>
</div>