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

        <!-- Introduction/Notice Section -->
        <div class="mpesapaywallpro-notice mpesapaywallpro-topnotice">
            <div class="notice-content">
                <h3>
                    <span class="dashicons dashicons-info"></span>
                    <?php esc_html_e('Welcome to MpesaPaywallPro', 'mpesapaywallpro'); ?>
                </h3>

                <p>
                    <?php esc_html_e(
                        'MpesaPaywallPro allows you to restrict access to content and require users to complete an M-Pesa payment before viewing it. To get started, enter your M-Pesa API credentials and configure the payment settings below.',
                        'mpesapaywallpro'
                    ); ?>
                </p>

                <p>
                    <?php esc_html_e(
                        'You can use either Sandbox (Test) credentials or Live (Production) credentials. If you are testing, select the Sandbox environment and use your test credentials. If you are accepting real payments, select the Production environment and use your live credentials.',
                        'mpesapaywallpro'
                    ); ?>
                </p>

                <p>
                    <?php esc_html_e(
                        'After entering your credentials, always click “Save Changes” first. Only test the API connection after your settings have been saved.',
                        'mpesapaywallpro'
                    ); ?>
                </p>

                <ul>
                    <li><?php esc_html_e('Enter your M-Pesa Consumer Key, Consumer Secret, Shortcode, and Passkey', 'mpesapaywallpro'); ?></li>
                    <li><?php esc_html_e('Select the correct environment: Sandbox for testing, Production for live payments', 'mpesapaywallpro'); ?></li>
                    <li><?php esc_html_e('Save your settings before testing the API connection', 'mpesapaywallpro'); ?></li>
                    <li><?php esc_html_e('Configure payment amounts and access duration after payment', 'mpesapaywallpro'); ?></li>
                    <li><?php esc_html_e('Control how locked content previews are displayed to users', 'mpesapaywallpro'); ?></li>
                </ul>

                <p style="color:#b32d2e; font-weight:600;">
                    <?php esc_html_e(
                        'WARNING: Never share your M-Pesa API credentials with third parties, contractors, or employees. Anyone with access to these details can initiate payments on your behalf.',
                        'mpesapaywallpro'
                    ); ?>
                </p>
            </div>

            <!-- Close button -->
            <div class="mpesapaywallpro-notice-close">
                <span class="dashicons dashicons-dismiss"></span>
            </div>
        </div>

        <!-- settings content based on the current tab -->
        <form method="post" action="options.php">
            <?php
            // Output security fields for the registered setting "mpesapaywallpro_options"
            settings_fields('mpesapaywallpro_settings_group');
            wp_nonce_field('mpesapaywallpro_nonce_action', 'mpesapaywallpro_nonce');

            // Output setting sections and their fields
            do_settings_sections('mpesapaywallpro_' . $current_tab);
            ?>
            <div class="mpesapaywallpro-settings-sections">
                <?php
                switch ($current_tab) {
                    case 'mpesa_setup':
                        include_once MPP_PATH . 'admin/partials/mpesa-setup.php';
                        break;
                    case 'paywall_settings':
                        include_once MPP_PATH . 'admin/partials/paywall-settings.php';
                        break;
                    case 'access_control':
                        include_once MPP_PATH . 'admin/partials/access-control.php';
                        break;
                }
                ?>
            </div>

            <?php
            submit_button();
            ?>
        </form>
    </div>
</div>