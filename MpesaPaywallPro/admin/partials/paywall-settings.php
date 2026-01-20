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

        <!-- checkbox to automatically lock new posts -->
        <tr>
            <th scope="row">
                <label for="auto_lock"><?php esc_html_e('Auto-Lock New Posts', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="checkbox"
                    id="auto_lock"
                    name="mpesapaywallpro_options[auto_lock]"
                    value="1"
                    <?php checked(isset($options['auto_lock']) ? $options['auto_lock'] : 0, 1); ?>>
                <span><?php esc_html_e('Enable to automatically lock all new posts behind the paywall', 'mpesapaywallpro'); ?></span>
            </td>
        </tr>

        <!-- default payment amount if automatically locked -->
        <tr>
            <th scope="row">
                <label for="default_amount"><?php esc_html_e('Default Payment Amount', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="number"
                    id="default_amount"
                    name="mpesapaywallpro_options[default_amount]"
                    value="<?php echo esc_attr($options['default_amount'] ?? '20'); ?>"
                    class="small-text"
                    min="1"
                    step="1">
                <span>KES</span>
                <p class="description">
                    <?php esc_html_e('Default amount in Kenyan Shillings for paywall access', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <!-- button colorpicker -->
        <tr>
            <th scope="row">
                <label for="button_color"><?php esc_html_e('Paywall Button Color', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <input type="color"
                    id="button_color"
                    name="mpesapaywallpro_options[button_color]"
                    value="<?php echo esc_attr($options['button_color'] ?? '#0073aa'); ?>"
                    class="mpesapaywallpro-color-field"
                    data-default-color="#0073aa">
                <p class="description">
                    <?php esc_html_e('Color of the paywall unlock button', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <!-- free content length -->
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
                <span><?php esc_html_e('words', 'mpesapaywallpro'); ?></span>
                <p class="description">
                    <?php esc_html_e('Number of words to show for free preview (0 = no preview)', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>

        <!-- paywall message -->
        <tr>
            <th scope="row">
                <label for="paywall_message"><?php esc_html_e('Paywall Message', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <?php
                $content = $options['paywall_message'] ?? 'Unlock this article and support quality content with a simple one-time payment.';
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

        <!-- payment expiry duration -->
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