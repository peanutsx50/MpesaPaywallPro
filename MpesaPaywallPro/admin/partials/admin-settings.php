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

$tabs = [
    'mpesa_setup' => [
        'label' => 'M-Pesa Setup',
        'icon'  => 'dashicons-money-alt',
    ],
    'paywall_settings' => [
        'label' => 'Paywall Settings',
        'icon'  => 'dashicons-lock',
    ],
    'access_control' => [
        'label' => 'Access Control',
        'icon'  => 'dashicons-shield',
    ],
];


$current_tab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $tabs) ? $_GET['tab'] : array_key_first($tabs);
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
    <div class="nav-tab-wrapper">
        <?php foreach ($tabs as $tab_key => $tab_info) : ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=mpesa-paywall-pro&tab=' . $tab_key)); ?>" class="nav-tab <?php echo $current_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons <?php echo esc_attr($tab_info['icon']); ?>"></span>
                <?php echo esc_html($tab_info['label']); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="mpesapaywallpro-admin-content">
        <!-- Admin settings form -->
        <div class="mpesapaywallpro-tab-content">

            <!-- Introduction/Notice Section -->
            <div class="mpesapaywallpro-notice notice-info">
                <div class="notice-content">
                    <h3>
                        <span class="dashicons dashicons-info"></span>
                        <?php esc_html_e('Welcome to MpesaPaywallPro', 'mpesapaywallpro'); ?>
                    </h3>
                    <p>
                        <?php esc_html_e('Thank you for choosing MpesaPaywallPro! Configure your M-Pesa payment gateway settings below to start accepting payments from your customers.', 'mpesapaywallpro'); ?>
                    </p>
                    <ul>
                        <li><?php esc_html_e('Set up your M-Pesa API credentials and environment', 'mpesapaywallpro'); ?></li>
                        <li><?php esc_html_e('Configure payment amounts', 'mpesapaywallpro'); ?></li>
                        <li><?php esc_html_e('Define how locked content previews are displayed', 'mpesapaywallpro'); ?></li>
                        <li><?php esc_html_e('Control how long users can access content after payment', 'mpesapaywallpro'); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        


    </div>