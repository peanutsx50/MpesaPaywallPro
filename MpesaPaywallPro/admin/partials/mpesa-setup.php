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
?>

<div class="mpesapaywallpro-settings-section">
    <h2 class="title">
        <span class="dashicons dashicons-businessperson"></span>
        <?php esc_html_e('M-Pesa API Configuration', 'mpesapaywallpro'); ?>
    </h2>
    
    <div class="mpesapaywallpro-notice notice-info">
        <p>
            <?php esc_html_e('Enter your M-Pesa Daraja API credentials. These are required to process payments through Safaricom.', 'mpesapaywallpro'); ?>
            <a href="https://developer.safaricom.co.ke/" target="_blank"><?php esc_html_e('Get API credentials', 'mpesapaywallpro'); ?></a>
        </p>
    </div>

    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="mpesa_consumer_key"><?php esc_html_e('Consumer Key', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="password" 
                       id="mpesa_consumer_key" 
                       name="mpesapaywallpro_options[mpesa_consumer_key]" 
                       value="<?php echo esc_attr($options['mpesa_consumer_key'] ?? ''); ?>" 
                       class="regular-text"
                       placeholder="Enter your M-Pesa Consumer Key">
                <p class="description">
                    <?php esc_html_e('Your M-Pesa Daraja API Consumer Key', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mpesa_consumer_secret"><?php esc_html_e('Consumer Secret', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="password" 
                       id="mpesa_consumer_secret" 
                       name="mpesapaywallpro_options[mpesa_consumer_secret]" 
                       value="<?php echo esc_attr($options['mpesa_consumer_secret'] ?? ''); ?>" 
                       class="regular-text"
                       placeholder="Enter your M-Pesa Consumer Secret">
                <p class="description">
                    <?php esc_html_e('Your M-Pesa Daraja API Consumer Secret', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mpesa_shortcode"><?php esc_html_e('Business Shortcode', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="text" 
                       id="mpesa_shortcode" 
                       name="mpesapaywallpro_options[mpesa_shortcode]" 
                       value="<?php echo esc_attr($options['mpesa_shortcode'] ?? ''); ?>" 
                       class="regular-text"
                       placeholder="e.g., 174379">
                <p class="description">
                    <?php esc_html_e('Your M-Pesa Business Shortcode (PayBill or Till Number)', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mpesa_passkey"><?php esc_html_e('Passkey', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="password" 
                       id="mpesa_passkey" 
                       name="mpesapaywallpro_options[mpesa_passkey]" 
                       value="<?php echo esc_attr($options['mpesa_passkey'] ?? ''); ?>" 
                       class="regular-text"
                       placeholder="Enter your M-Pesa Passkey">
                <p class="description">
                    <?php esc_html_e('Your M-Pesa Daraja API Passkey', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mpesa_env"><?php esc_html_e('Environment', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <select id="mpesa_env" name="mpesapaywallpro_options[mpesa_env]" class="regular-text">
                    <option value="sandbox" <?php selected($options['mpesa_env'] ?? 'sandbox', 'sandbox'); ?>>
                        <?php esc_html_e('Sandbox (Testing)', 'mpesapaywallpro'); ?>
                    </option>
                    <option value="production" <?php selected($options['mpesa_env'] ?? '', 'production'); ?>>
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

<?php
// Add inline JavaScript for testing connection
add_action('admin_footer', function() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Copy callback URL
        $('.copy-callback-url').on('click', function() {
            var copyText = document.getElementById($(this).data('clipboard-target').substring(1));
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            
            var originalText = $(this).html();
            $(this).html('<span class="dashicons dashicons-yes"></span> Copied!');
            setTimeout(function() {
                $(this).html(originalText);
            }.bind(this), 2000);
        });
        
        // Test M-Pesa connection
        $('#test-mpesa-connection').on('click', function() {
            var $button = $(this);
            var $result = $('#test-connection-result');
            
            $button.prop('disabled', true).text('Testing...');
            $result.hide().removeClass('success error');
            
            $.post(ajaxurl, {
                action: 'test_mpesa_connection',
                security: '<?php echo wp_create_nonce("test_mpesa_connection_nonce"); ?>'
            }, function(response) {
                $result.show();
                if (response.success) {
                    $result.addClass('success').html('<p><span class="dashicons dashicons-yes"></span> ' + response.data.message + '</p>');
                } else {
                    $result.addClass('error').html('<p><span class="dashicons dashicons-no"></span> ' + response.data.message + '</p>');
                }
                $button.prop('disabled', false).text('Test API Connection');
            }).fail(function() {
                $result.show().addClass('error').html('<p><span class="dashicons dashicons-no"></span> Connection test failed. Please try again.</p>');
                $button.prop('disabled', false).text('Test API Connection');
            });
        });
    });
    </script>
    <style>
    .test-result {
        margin-top: 10px;
        padding: 10px;
        border-radius: 4px;
    }
    .test-result.success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    .test-result.error {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    .test-result p {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    </style>
    <?php
});
?>