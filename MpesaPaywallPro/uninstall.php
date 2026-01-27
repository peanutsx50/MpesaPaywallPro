<?php

/**
 * MpesaPaywallPro Plugin Uninstaller
 *
 * Handles the complete cleanup and removal of all MpesaPaywallPro plugin data,
 * settings, and custom content when the plugin is uninstalled from WordPress.
 *
 * This file is triggered by WordPress when a user clicks "Delete" on the plugin
 * in the WordPress admin. It safely removes all plugin-related data including:
 * - Custom post types and associated metadata
 * - Plugin options and settings
 * - Transients and cached data
 * - Works with both single site and multisite installations
 *
 * @package    MpesaPaywallPro
 * @subpackage MpesaPaywallPro/includes
 * @author     SurgeTech <https://surgetech.co.ke>
 * @license    GPL v2 or later
 * @link       https://surgetech.co.ke/mpesapaywallpro
 * @since      1.0.0
 */

// If uninstall not called from WordPress, then exit.
if (! defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

/**
 * Delete all custom post type 'mpesa'
 */
function mpesapaywallpro_delete_custom_posts()
{
	$posts = get_posts(array(
		'post_type'   => 'mpesa',
		'numberposts' => -1,
		'post_status' => 'any',
	));

	foreach ($posts as $post) {
		wp_delete_post($post->ID, true); // Delete permanently
	}
}

/**
 * Delete post meta associated with content locking
 */
function mpesapaywallpro_delete_post_meta()
{
	$post_meta_keys = array(
		'mpp_is_locked',
		'mpp_price',
	);

	foreach ($post_meta_keys as $meta_key) {
		delete_post_meta_by_key($meta_key);
	}
}

/**
 * Delete plugin options from options table
 */
function mpesapaywallpro_delete_plugin_options()
{
	delete_option('mpesapaywallpro_options');
	delete_site_option('mpesapaywallpro_options');
}

/**
 * Delete plugin transients
 */
function mpesapaywallpro_delete_transients()
{
	global $wpdb;

	$transient_name_like = 'mpesapaywallpro_license_%';

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			'_transient_' . $transient_name_like,
			'_transient_timeout_' . $transient_name_like
		)
	);
}

/**
 * Main uninstall function that coordinates all cleanup operations
 */
function mpesapaywallpro_uninstall_plugin()
{
	mpesapaywallpro_delete_custom_posts();
	mpesapaywallpro_delete_post_meta();
	mpesapaywallpro_delete_plugin_options();
	mpesapaywallpro_delete_transients();
}

// Execute the uninstall process
mpesapaywallpro_uninstall_plugin();
