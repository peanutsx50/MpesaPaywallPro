<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://festuswp.gumroad.com/l/MpesaPaywallPro
 * @since      1.0.0
 *
 * @package    MpesaPaywallPro
 * @subpackage MpesaPaywallPro/admin/partials
 */

if (!defined('ABSPATH')) {
    exit;
}
?>


<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <!-- Admin top banner -->
    <div class="mpesapaywallpro-admin-banner">
        <div class="mpesapaywallpro-banner-content">
            <div class="mpesapaywallpro-banner-left">
                <h1>
                    <span class="mpesapaywallpro-icon">💳</span>
                    <?php echo esc_html(get_admin_page_title()); ?>
                    <span class="mpesapaywallpro-version">v<?php echo esc_html(MPP_VERSION); ?></span>
                </h1>
                <p class="mpesapaywallpro-banner-description">
                    <?php esc_html_e('Seamlessly integrate M-Pesa payments for premium content, memberships, and digital products on your WordPress site.', 'mpesapaywallpro'); ?>
                </p>
            </div>
            <div class="mpesapaywallpro-banner-right">
                <div class="mpesapaywallpro-banner-actions">
                    <a href="https://festuswp.gumroad.com/l/MpesaPaywallPro" target="_blank" class="button button-primary">
                        <div class="mpesapaywallpro-features-button">
                            <span class="dashicons dashicons-external"></span>
                            <?php esc_html_e('View Premium Features', 'mpesapaywallpro'); ?>
                        </div>
                    </a>
                    <a href="https://festuswp.gumroad.com/l/MpesaPaywallPro" target="_blank" class="button">
                        <div class="mpesapaywallpro-features-button">
                            <span class="dashicons dashicons-book"></span>
                            <?php esc_html_e('Documentation', 'mpesapaywallpro'); ?>
                        </div>

                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin tabbed navigation -->

    <!-- Admin settings form -->

</div>