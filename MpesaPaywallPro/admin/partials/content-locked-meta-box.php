<?php

/**
 * The admin content locked meta box partial file.
 * 
 * This file is responsible for rendering the content locked meta box
 * in the WordPress admin post editor screen. It allows administrators
 * to specify whether a post's content is locked behind the M-Pesa paywall.
 * 
 * @since    1.0.0
 * @package  MpesaPaywallPro
 * 
 * @wordpress-admin
 * @subpackage MpesaPaywallPro/admin/partials
 * 
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

//add nonce field so we can retreive it later when saving
wp_nonce_field(basename(__FILE__), 'mpp_content_locked_meta_box_nonce');

// retrieve current value of the content locked meta field
$is_locked = get_post_meta($post->ID, 'mpp_is_locked', true);
$price     = get_post_meta($post->ID, 'mpp_price', true);

?>

<div>
    <p>
        <label>
            <input type="checkbox" name="mpp_is_locked" value="1" <?php checked($is_locked, '1'); ?>>
            <?php esc_html_e('Lock this post behind a paywall', 'mpesapaywallpro'); ?>
        </label>
    </p>

    <p>
        <label for="mpp_price">
            <?php esc_html_e('Price (KES)', 'mpesapaywallpro'); ?>
        </label>
        <input
            type="number"
            id="mpp_price"
            name="mpp_price"
            value="<?php echo esc_attr($price); ?>"
            min="1"
            step="1"
            style="width:100%;"
            <?php disabled(!$is_locked); ?>>
    </p>
</div>