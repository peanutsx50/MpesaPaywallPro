<?php
/**
 * Access Control Settings Template
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
        <span class="dashicons dashicons-shield"></span>
        <?php esc_html_e('Access Control & Security', 'mpesapaywallpro'); ?>
    </h2>
    
    <div class="mpesapaywallpro-notice notice-warning">
        <p>
            <?php esc_html_e('Configure security settings and access controls for your paywall system.', 'mpesapaywallpro'); ?>
        </p>
    </div>

    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="require_login"><?php esc_html_e('Require User Login', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <label>
                    <input type="checkbox" 
                           id="require_login" 
                           name="mpesapaywallpro_options[require_login]" 
                           value="1" 
                           <?php checked($options['require_login'] ?? 0, 1); ?>>
                    <?php esc_html_e('Users must be logged in to make payments', 'mpesapaywallpro'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('If enabled, anonymous users will be prompted to login/register before payment', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="allowed_user_roles"><?php esc_html_e('Exempt User Roles', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <?php
                $roles = wp_roles()->get_names();
                $selected_roles = $options['allowed_user_roles'] ?? ['administrator'];
                ?>
                <fieldset>
                    <?php foreach ($roles as $role_value => $role_name): ?>
                        <label style="display: block; margin-bottom: 5px;">
                            <input type="checkbox" 
                                   name="mpesapaywallpro_options[allowed_user_roles][]" 
                                   value="<?php echo esc_attr($role_value); ?>"
                                   <?php checked(in_array($role_value, (array)$selected_roles)); ?>>
                            <?php echo esc_html(translate_user_role($role_name)); ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <p class="description">
                    <?php esc_html_e('Selected user roles will bypass the paywall without payment', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="enable_auto_unlock"><?php esc_html_e('Auto Content Unlock', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <label>
                    <input type="checkbox" 
                           id="enable_auto_unlock" 
                           name="mpesapaywallpro_options[enable_auto_unlock]" 
                           value="1" 
                           <?php checked($options['enable_auto_unlock'] ?? 1, 1); ?>>
                    <?php esc_html_e('Automatically unlock content after successful payment', 'mpesapaywallpro'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('If disabled, admin must manually approve payments', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="payment_timeout"><?php esc_html_e('Payment Timeout', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="number" 
                       id="payment_timeout" 
                       name="mpesapaywallpro_options[payment_timeout]" 
                       value="<?php echo esc_attr($options['payment_timeout'] ?? '300'); ?>" 
                       class="small-text"
                       min="60"
                       step="1">
                <span><?php esc_html_e('seconds', 'mpesapaywallpro'); ?></span>
                <p class="description">
                    <?php esc_html_e('Time allowed for user to complete M-Pesa payment', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="max_payment_attempts"><?php esc_html_e('Max Payment Attempts', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="number" 
                       id="max_payment_attempts" 
                       name="mpesapaywallpro_options[max_payment_attempts]" 
                       value="<?php echo esc_attr($options['max_payment_attempts'] ?? '3'); ?>" 
                       class="small-text"
                       min="1"
                       step="1">
                <p class="description">
                    <?php esc_html_e('Maximum payment attempts per user per hour', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="enable_debug_log"><?php esc_html_e('Debug Logging', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <label>
                    <input type="checkbox" 
                           id="enable_debug_log" 
                           name="mpesapaywallpro_options[enable_debug_log]" 
                           value="1" 
                           <?php checked($options['enable_debug_log'] ?? 0, 1); ?>>
                    <?php esc_html_e('Enable debug logging for payment transactions', 'mpesapaywallpro'); ?>
                </label>
                <?php if (!empty($options['enable_debug_log'])): ?>
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=mpesapaywallpro-logs')); ?>" class="button button-secondary">
                            <span class="dashicons dashicons-media-text"></span>
                            <?php esc_html_e('View Debug Logs', 'mpesapaywallpro'); ?>
                        </a>
                    </p>
                <?php endif; ?>
                <p class="description">
                    <?php esc_html_e('Log all payment transactions for debugging purposes', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="enable_webhook_verification"><?php esc_html_e('Webhook Verification', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <label>
                    <input type="checkbox" 
                           id="enable_webhook_verification" 
                           name="mpesapaywallpro_options[enable_webhook_verification]" 
                           value="1" 
                           <?php checked($options['enable_webhook_verification'] ?? 1, 1); ?>>
                    <?php esc_html_e('Verify M-Pesa webhook signatures for security', 'mpesapaywallpro'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('Recommended for production environments', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>
    </table>

    <div class="mpesapaywallpro-security-tips">
        <h3>
            <span class="dashicons dashicons-lightbulb"></span>
            <?php esc_html_e('Security Tips', 'mpesapaywallpro'); ?>
        </h3>
        <ul>
            <li><?php esc_html_e('Always use SSL/HTTPS for your website when processing payments', 'mpesapaywallpro'); ?></li>
            <li><?php esc_html_e('Keep your M-Pesa API credentials secure and never share them', 'mpesapaywallpro'); ?></li>
            <li><?php esc_html_e('Regularly update WordPress and the MpesaPaywallPro plugin', 'mpesapaywallpro'); ?></li>
            <li><?php esc_html_e('Monitor transaction logs for suspicious activity', 'mpesapaywallpro'); ?></li>
            <li><?php esc_html_e('Use IP whitelisting for internal testing only', 'mpesapaywallpro'); ?></li>
        </ul>
    </div>

    <div class="mpesapaywallpro-clear-cache">
        <h3>
            <span class="dashicons dashicons-update"></span>
            <?php esc_html_e('Cache Management', 'mpesapaywallpro'); ?>
        </h3>
        <p><?php esc_html_e('Clear paywall access cache if users are not seeing unlocked content after payment:', 'mpesapaywallpro'); ?></p>
        <button type="button" id="clear-paywall-cache" class="button button-secondary">
            <?php esc_html_e('Clear Access Cache', 'mpesapaywallpro'); ?>
        </button>
        <span id="cache-clear-result" style="margin-left: 10px;"></span>
    </div>
</div>

<?php
// Add inline JavaScript
add_action('admin_footer', function() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Clear paywall cache
        $('#clear-paywall-cache').on('click', function() {
            var $button = $(this);
            var $result = $('#cache-clear-result');
            
            $button.prop('disabled', true).text('Clearing...');
            
            $.post(ajaxurl, {
                action: 'clear_paywall_cache',
                security: '<?php echo wp_create_nonce("clear_paywall_cache_nonce"); ?>'
            }, function(response) {
                if (response.success) {
                    $result.html('<span style="color: green;"><span class="dashicons dashicons-yes"></span> ' + response.data.message + '</span>');
                } else {
                    $result.html('<span style="color: red;"><span class="dashicons dashicons-no"></span> ' + response.data.message + '</span>');
                }
                $button.prop('disabled', false).text('Clear Access Cache');
                
                // Clear message after 3 seconds
                setTimeout(function() {
                    $result.html('');
                }, 3000);
            }).fail(function() {
                $result.html('<span style="color: red;"><span class="dashicons dashicons-no"></span> Failed to clear cache</span>');
                $button.prop('disabled', false).text('Clear Access Cache');
            });
        });
        
        // Show/hide IP whitelist help
        $('#ip_whitelist').on('focus', function() {
            $(this).parent().append('<div class="ip-format-help">Format: 192.168.1.1 or 192.168.1.0/24 for subnet</div>');
        }).on('blur', function() {
            $(this).parent().find('.ip-format-help').remove();
        });
    });
    </script>
    <style>
    .mpesapaywallpro-security-tips ul {
        margin-left: 20px;
        list-style-type: disc;
    }
    .mpesapaywallpro-security-tips li {
        margin-bottom: 5px;
    }
    .ip-format-help {
        background: #f0f0f0;
        padding: 5px 10px;
        border-radius: 3px;
        margin-top: 5px;
        font-size: 12px;
        color: #666;
    }
    </style>
    <?php
});
?>