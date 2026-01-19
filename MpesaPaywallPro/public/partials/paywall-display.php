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
$price     = get_post_meta($post_id, 'mpp_price', true);
?>

