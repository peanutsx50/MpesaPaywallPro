<?php

/**
 * The phone number modal partial file.
 * This file is responsible for rendering the phone number
 * input modal in the public-facing side of the website.
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
?>
<!-- Modal HTML -->
<div class="mpp-modal-overlay" id="mpp-phone-modal">
    <div class="mpp-modal">
        <button class="mpp-modal-close" id="mpp-modal-close" aria-label="Close">&times;</button>

        <div class="mpp-modal-header">
            <h3 class="mpp-modal-title"><?php _e('Enter your M-Pesa number', 'mpesapaywallpro'); ?></h3>
            <p class="mpp-modal-subtitle">
                <?php echo sprintf(__('You\'ll receive an M-Pesa prompt to pay KES %s', 'mpesapaywallpro'), esc_html($price)); ?>
            </p>
        </div>

        <form id="mpp-phone-form">
            <div class="mpp-form-group">
                <label class="mpp-form-label" for="mpp-phone-number">
                    <?php _e('Phone Number', 'mpesapaywallpro'); ?>
                </label>
                <input
                    type="tel"
                    id="mpp-phone-number"
                    class="mpp-phone-input"
                    placeholder="254712345678"
                    maxlength="12"
                    required />
                <p class="mpp-phone-hint"><?php _e('Enter your Safaricom number (e.g., 254712345678)', 'mpesapaywallpro'); ?></p>
                <p class="mpp-error-message" id="mpp-phone-error"></p>
            </div>

            <div class="mpp-modal-actions">
                <button type="button" class="mpp-btn mpp-btn-secondary" id="mpp-cancel-btn">
                    <?php _e('Cancel', 'mpesapaywallpro'); ?>
                </button>
                <button type="submit" class="mpp-btn mpp-btn-primary" id="mpp-submit-btn">
                    <?php _e('Continue', 'mpesapaywallpro'); ?>
                </button>
            </div>
        </form>
    </div>
</div>