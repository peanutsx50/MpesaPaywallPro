<?php
/**
 * Paywall Settings Template
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
        <span class="dashicons dashicons-lock"></span>
        <?php esc_html_e('Paywall Configuration', 'mpesapaywallpro'); ?>
    </h2>
    
    <div class="mpesapaywallpro-notice notice-info">
        <p>
            <?php esc_html_e('Configure how your paywall works, including payment amounts, content restriction, and user experience.', 'mpesapaywallpro'); ?>
        </p>
    </div>

    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="default_amount"><?php esc_html_e('Default Payment Amount', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="number" 
                       id="default_amount" 
                       name="mpesapaywallpro_options[default_amount]" 
                       value="<?php echo esc_attr($options['default_amount'] ?? '100'); ?>" 
                       class="small-text"
                       min="1"
                       step="1">
                <span>KES</span>
                <p class="description">
                    <?php esc_html_e('Default amount in Kenyan Shillings for paywall access', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="payment_description"><?php esc_html_e('Payment Description', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="text" 
                       id="payment_description" 
                       name="mpesapaywallpro_options[payment_description]" 
                       value="<?php echo esc_attr($options['payment_description'] ?? 'Premium Content Access'); ?>" 
                       class="regular-text"
                       placeholder="e.g., Premium Article Access">
                <p class="description">
                    <?php esc_html_e('Description that appears on M-Pesa statement', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="excerpt_length"><?php esc_html_e('Free Content Length', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="number" 
                       id="excerpt_length" 
                       name="mpesapaywallpro_options[excerpt_length]" 
                       value="<?php echo esc_attr($options['excerpt_length'] ?? '100'); ?>" 
                       class="small-text"
                       min="0"
                       step="1">
                <span><?php esc_html_e('characters', 'mpesapaywallpro'); ?></span>
                <p class="description">
                    <?php esc_html_e('Number of characters to show for free preview (0 = no preview)', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="paywall_message"><?php esc_html_e('Paywall Message', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <?php
                $content = $options['paywall_message'] ?? 'This content is locked. Please make a payment to access the full content.';
                wp_editor($content, 'paywall_message', [
                    'textarea_name' => 'mpesapaywallpro_options[paywall_message]',
                    'textarea_rows' => 5,
                    'media_buttons' => false,
                    'teeny' => true,
                    'quicktags' => false
                ]);
                ?>
                <p class="description">
                    <?php esc_html_e('Message displayed when content is locked behind paywall', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="payment_expiry"><?php esc_html_e('Access Expiry', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="number" 
                       id="payment_expiry" 
                       name="mpesapaywallpro_options[payment_expiry]" 
                       value="<?php echo esc_attr($options['payment_expiry'] ?? '30'); ?>" 
                       class="small-text"
                       min="0"
                       step="1">
                <span><?php esc_html_e('days (0 = never expires)', 'mpesapaywallpro'); ?></span>
                <p class="description">
                    <?php esc_html_e('Number of days before payment access expires', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>