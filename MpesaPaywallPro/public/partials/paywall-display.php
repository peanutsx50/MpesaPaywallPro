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
$price = get_post_meta($post_id, 'mpp_price', true);
?>
<div class="mpp-paywall-container">
    <h3 class="mpp-paywall-title">
        <?php _e('Read the full story', 'mpesapaywallpro'); ?>
    </h3>

    <p class="mpp-paywall-description">
        <?php _e('Unlock this article and support quality content with a simple one-time payment', 'mpesapaywallpro'); ?>
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

        <button id="mpp-pay-button">
            <?php _e('Unlock with M-Pesa', 'mpesapaywallpro'); ?>
        </button>
    </div>

    <div id="mpp-payment-status"></div>
</div>