<?php

/**
 * The public paywall display partial file.
 * This file is responsible for rendering the paywall display
 * in the public-facing side of the website.
 * 
 * @since    1.0.0
 * @package  MpesaPaywallPro
 * 
 * @wordpress-public
 * @subpackage MpesaPaywallPro/public/partials
 * 
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

// Get the current post ID
$post_id = get_the_ID();
// retrieve current value of the content locked meta field
// $price is coming from render_paywall() function in MpesaPaywallProPublic.php and is passed to the paywall-display.php file when it is required
$buttonColor = get_option('mpesapaywallpro_options')['button_color'] ?? null;
$paywallMessage = get_option('mpesapaywallpro_options')['paywall_message'] ?? esc_html__('This content is locked to help us continue creating valuable stories. Unlock full access with a secure M-Pesa payment.', 'mpesapaywallpro');

// Get WordPress theme colors
$primary_color = get_theme_mod('primary_color');
$accent_color = get_theme_mod('accent_color');
$link_color = get_theme_mod('link_color');

// Fallback to button color option or theme mod
if (!$buttonColor) {
    $buttonColor = $primary_color ?: $accent_color ?: $link_color ?: '#111827';
}

?>
<div class="mpp-paywall-container">
    <h3 class="mpp-paywall-title">
        <?php esc_html_e('Read the full story', 'mpesapaywallpro'); ?>
    </h3>

    <p class="mpp-paywall-description">
        <?php echo esc_html($paywallMessage); ?>
    </p>

    <ul class="mpp-benefits-list">
        <li class="mpp-benefit-item"><?php _e('Instant access to full article', 'mpesapaywallpro'); ?></li>
        <li class="mpp-benefit-item"><?php _e('Read on any device', 'mpesapaywallpro'); ?></li>
        <li class="mpp-benefit-item"><?php _e('No subscription required', 'mpesapaywallpro'); ?></li>
        <li class="mpp-benefit-item"><?php _e('Secure M-Pesa payment', 'mpesapaywallpro'); ?></li>
    </ul>

    <div class="mpp-paywall-action">
        <div class="mpp-price-tag">
            <small>KES</small> <?php echo esc_html($price); ?>
        </div>

        <button id="mpp-pay-button" type="button" style="background-color: <?php echo esc_attr($buttonColor); ?>;">
            <?php _e('Unlock with M-Pesa', 'mpesapaywallpro'); ?>
        </button>
    </div>

    <div id="mpp-payment-status"></div>
</div>
<?php require MPP_PATH . 'public/partials/phone-number-modal.php'; ?>