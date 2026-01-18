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
    <div class="mpesa-paywall-pro-admin-banner">

    </div>

    <!-- Admin tabbed navigation -->

    <!-- Admin settings form -->


    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <p class="description">
        <?php esc_html_e('Configure the MpesaPaywallPro plugin settings below.', 'mpesapaywallpro'); ?>
    </p>

</div>
