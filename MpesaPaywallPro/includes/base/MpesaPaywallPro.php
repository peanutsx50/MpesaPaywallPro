<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://surgetech.co.ke
 * @since      1.0.0
 *
 * @package    mpesapaywallpro
 * @subpackage mpesapaywallpro/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    mpesapaywallpro
 * @subpackage mpesapaywallpro/includes
 * @author     SurgeTech <admin@surgetech.co.ke>
 */

namespace MpesaPaywallPro\base;

use MpesaPaywallPro\admin\MpesaPaywallProAdmin;
use MpesaPaywallPro\public\MpesaPaywallProPublic;


class MpesaPaywallPro
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      mpesapaywallpro_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $mpesapaywallpro    The string used to uniquely identify this plugin.
	 */
	protected $mpesapaywallpro;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('MPP_VERSION')) {
			$this->version = MPP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->mpesapaywallpro = 'MpesaPaywallPro';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - MpesaPaywallProLoader. Orchestrates the hooks of the plugin.
	 * - MpesaPaywallProI18n. Defines internationalization functionality.
	 * - MpesaPaywallProAdmin. Defines all hooks for the admin area.
	 * - MpesaPaywallProPublic. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		$this->loader = new MpesaPaywallProLoader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the mpesapaywallpro_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new MpesaPaywallProI18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new MpesaPaywallProAdmin($this->get_mpesapaywallpro(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		// register admin page
		$this->loader->add_action('admin_menu', $plugin_admin, 'register_admin_page');

		//register custom meta box
		$this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_custom_meta_field');

		//save meta box data
		$this->loader->add_action('save_post', $plugin_admin, 'save_meta_box_data');

		// save settings
		$this->loader->add_action('admin_init', $plugin_admin, 'save_settings');

		//localize scripts with ajax url
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'localize_scripts');

		// Register AJAX handlers for M-Pesa payment processing
		// wp_ajax_nopriv allows non-authenticated users to process payments
		// wp_ajax allows authenticated users to process payments
		$this->loader->add_action('wp_ajax_nopriv_mpp_admin_test_connection', $plugin_admin, 'test_connection');
		$this->loader->add_action('wp_ajax_mpp_admin_test_connection', $plugin_admin, 'test_connection');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality of the plugin.
	 *
	 * This method sets up all public-facing hooks including:
	 * - Frontend script and style enqueuing
	 * - Content filtering for paywall display
	 * - REST API endpoints for payment processing
	 * - AJAX handlers for M-Pesa payment processing (both authenticated and non-authenticated users)
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   void
	 */
	private function define_public_hooks()
	{

		$plugin_public = new MpesaPaywallProPublic($this->get_mpesapaywallpro(), $this->get_version());

		// Enqueue frontend styles and scripts
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

		//localize script with ajax url
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'localize_scripts');

		// Filter post content to display paywall for locked content
		$this->loader->add_filter('the_content', $plugin_public, 'filter_post_content');

		// Register REST API endpoints for payment processing
		$this->loader->add_action('rest_api_init', $plugin_public, 'register_ajax_endpoints');

		// Register AJAX handlers for M-Pesa payment processing
		// wp_ajax_nopriv allows non-authenticated users to process payments
		// wp_ajax allows authenticated users to process payments
		$this->loader->add_action('wp_ajax_nopriv_mpp_process_payment', $plugin_public, 'process_payment');
		$this->loader->add_action('wp_ajax_mpp_process_payment', $plugin_public, 'process_payment');

		//add license check on admin init
		$this->loader->add_action('after_plugin_row_' . MPP_BASENAME, $this, 'display_license_notice', 10, 3);

		//disable update if license fails
		$this->loader->add_filter('pre_set_site_transient_update_plugins', $this, 'block_updates');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_mpesapaywallpro()
	{
		return $this->mpesapaywallpro;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    mpesapaywallpro_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}

	/**
	 * Display a license status notice on the plugins page.
	 *
	 * This method is hooked to 'after_plugin_row_' action and displays a notice
	 * below the MpesaPaywallPro plugin row in the plugins list table. The notice
	 * appears only when the license is invalid, missing, or has an error.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string $plugin_file The plugin file path.
	 * @param    array  $plugin_data An array of plugin data.
	 * @param    string $status      The plugin status.
	 * @return   void
	 */
	public function display_license_notice($plugin_file, $plugin_data, $status)
	{
		$license_status = $this->get_cached_license_status();

		if ($license_status === 'valid') {
			return; // Don't show anything if license is valid
		}

		$message = $this->get_license_message($license_status);

		echo '<tr class="plugin-update-tr active" id="mpesapaywallpro-license-notice">
        <td colspan=4 class="plugin-update colspanchange">
            <div class="update-message notice inline notice-error notice-alt">
                <p>' . $message . '</p>
            </div>
        </td>
    </tr>';
	}

	/**
	 * Get the cached license status or verify it with the license server.
	 *
	 * Retrieves the license key from plugin options and checks the cached
	 * license status. If no cache exists, it verifies the license with the
	 * remote license server and caches the result for 12 hours (if valid)
	 * or 1 hour (if invalid).
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   string License status: 'valid', 'invalid', 'missing', or 'error'.
	 */
	private function get_cached_license_status()
	{
		$license_key = get_option('mpesapaywallpro_options')['license_key'] ?? '';

		if (empty($license_key)) {
			return 'missing';
		}

		// Cache based on license key hash
		$cache_key = 'mpesapaywallpro_license_' . md5($license_key);
		$cached_status = get_transient($cache_key);

		if ($cached_status !== false) {
			return $cached_status;
		}

		$status = $this->verify_license_with_server($license_key);

		// Cache for 12 hours if valid, 1 hour if invalid
		$cache_time = ($status === 'valid') ? 12 * HOUR_IN_SECONDS : HOUR_IN_SECONDS;
		set_transient($cache_key, $status, $cache_time);

		return $status;
	}

	/**
	 * Verify the license key with the remote license server.
	 *
	 * Makes a POST request to the license server with the license key to verify
	 * its validity. Returns the verification status without caching, allowing the
	 * caller to handle caching as needed.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string $license_key The license key to verify.
	 * @return   string License status: 'valid', 'invalid', or 'error'.
	 *                   - 'valid': License is active and valid.
	 *                   - 'invalid': License key is not valid or has expired.
	 *                   - 'error': Server communication error occurred.
	 */
	private function verify_license_with_server($license_key)
	{
		$response = wp_remote_post(MPP_LICENSE_SERVER, array(
			'method'  => 'POST',
			'body'    => json_encode(array('license_key' => sanitize_text_field($license_key))),
			'timeout' => 15, // Reduced timeout
			'headers' => array('Content-Type' => 'application/json'),
		));

		if (is_wp_error($response)) {
			return 'error';
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (isset($data['status']) && $data['status'] === 'valid') {
			return 'valid';
		}

		return 'invalid';
	}

	/**
	 * Get a user-friendly license status message.
	 *
	 * Returns a localized message based on the license status. Messages include
	 * a link to the plugin settings page where users can add or update their
	 * license key.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string $status The license status: 'valid', 'invalid', 'missing', or 'error'.
	 * @return   string Localized HTML message. Empty string if status is 'valid'.
	 */
	private function get_license_message($status)
	{
		$settings_url = admin_url('admin.php?page=mpesa-paywall-pro&tab=paywall_settings');

		switch ($status) {
			case 'missing':
				return sprintf(
					__('⚠️ License key is missing. <a href="%s">Add your license key</a> to enable updates and support.', 'mpesapaywallpro'),
					esc_url($settings_url)
				);

			case 'invalid':
				return sprintf(
					__('⚠️ Your license key is invalid or has expired. <a href="%s">Please check your license details</a>.', 'mpesapaywallpro'),
					esc_url($settings_url)
				);

			case 'error':
				return __('⚠️ Unable to verify license. Please try again later.', 'mpesapaywallpro');

			default:
				return '';
		}
	}

	/**
	 * Clear the cached license status.
	 *
	 * Removes the transient that stores the cached license verification status.
	 * This method should be called whenever the license key is updated to ensure
	 * the next verification uses fresh data from the license server.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function clear_license_cache()
	{
		$license_key = get_option('mpesapaywallpro_options')['license_key'] ?? '';
		if (!empty($license_key)) {
			delete_transient('mpesapaywallpro_license_' . md5($license_key));
		}
	}

	/**
	 * Block plugin updates if the license verification fails.
	 *
	 * This method is hooked to the 'pre_set_site_transient_update_plugins' filter.
	 * It checks if the license is valid before allowing plugin updates to be set.
	 * If the license status is not 'valid', it removes the plugin's update
	 * information from the WordPress update transient, preventing users from
	 * updating the plugin until the license is verified.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    object $transient The site transient data for plugin updates.
	 * @return   object The modified plugin updates transient with MpesaPaywallPro
	 *                  updates removed if license is invalid.
	 */
	public function block_updates( $transient ) {
		$license_status = $this->get_cached_license_status();
		
		if ( $license_status !== 'valid' ) {
			if ( isset( $transient->response[MPP_BASENAME] ) ) {
				unset( $transient->response[MPP_BASENAME] );
			}
		}
		
		return $transient;
	}
}
