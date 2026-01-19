<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://festuswp.gumroad.com/l/MpesaPaywallPro
 * @since      1.0.0
 *
 * @package    MpesaPaywallPro
 * @subpackage MpesaPaywallPro/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    MpesaPaywallPro
 * @subpackage MpesaPaywallPro/admin
 * @author     Festus Murimi <murimifestus09@gmail.com>
 */

namespace MpesaPaywallPro\admin;

use MpesaPaywallPro\base\MpesaPaywallPro;

class MpesaPaywallProAdmin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, MPP_URL . 'admin/css/admin-settings.css', array(), (float) $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, MPP_URL . 'admin/js/admin-settings.js', array('jquery'), (float) $this->version, false);
		wp_enqueue_script($this->plugin_name . '-meta-box', MPP_URL . 'admin/js/content-locked-meta-box.js', array('jquery'), (float) $this->version, false);
	}

	/**
	 * Register the admin page.
	 *
	 * @since    1.0.0
	 */
	public function register_admin_page()
	{
		/**
		 * This function adds a new admin page to the WordPress dashboard.
		 * 
		 * The admin page serves as the main settings interface for the MpesaPaywallPro plugin,
		 * allowing administrators to configure M-Pesa integration, set paywall options,
		 * manage payment settings, and monitor transaction history.
		 * 
		 * Parameters explained:
		 * - 'MpesaPaywallPro': The page title displayed in the browser tab and at the top of the page
		 * - 'MpesaPaywallPro': The text label shown in the WordPress admin menu sidebar
		 * - 'manage_options': WordPress capability required to access this page (admin-only)
		 * - 'mpesa-paywall-pro': Unique slug identifier for the page (used in URLs and references)
		 * - array($this, 'display_admin_page'): Callback function that renders the page content
		 * - 'dashicons-admin-generic': Icon displayed next to the menu item (from WordPress dashicons)
		 * - 81: Menu position in the dashboard (higher numbers appear lower in the menu)
		 * 
		 * @return void
		 */
		add_menu_page(
			'MpesaPaywallPro settings', // Page title
			'MpesaPaywallPro', // Menu title
			'manage_options', // Capability
			'mpesa-paywall-pro', // Menu slug
			array($this, 'display_admin_page'), // Callback function
			'dashicons-admin-generic', // Icon URL
			81 // Position
		);
	}

	/**
	 * Display the admin page content.
	 *
	 * @since    1.0.0
	 */
	public function display_admin_page()
	{
		// Include the admin page HTML template
		$admin_template = MPP_PATH . 'admin/partials/admin-settings.php';
		require_once $admin_template;
	}

	/**
	 * Register custom meta box for post paywall settings.
	 *
	 * Adds a meta box to the WordPress post editor that allows administrators
	 * to configure paywall settings for individual posts, including content
	 * lock status and pricing information.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function add_custom_meta_field()
	{
		// add custom meta field to get content locked status
		add_meta_box(
			'mpp_meta_box', // Unique ID
			__('MpesaPaywall', 'mpesapaywallpro'), // Box title
			array($this, 'render_content_meta_box'), // Content callback, must be of type callable
			'post', // Post type
			'side', // Context
			'high' // Priority
		);
		// add custom meta field to get content price
	}

	/**
	 * Render the content of the paywall meta box.
	 *
	 * Displays the HTML form fields for configuring paywall settings on individual posts.
	 * The actual HTML markup is loaded from a separate partial template file to keep
	 * the class file focused on business logic rather than presentation.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function render_content_meta_box()
	{
		require_once MPP_PATH . 'admin/partials/content-locked-meta-box.php';
	}

	//save meta box data
	public function save_meta_box_data($post_id)
	{
		// Verify nonce prevent cross site request forgery (CSRF)
		if (
			!isset($_POST['mpp_paywall_nonce']) ||
			!wp_verify_nonce($_POST['mpp_paywall_nonce'], 'mpp_save_paywall_meta')
		) {
			return;
		}

		// Check for autosave, prevent data from being saved during autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		// Check user permissions, if user can't edit post, exit
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		// Lock status, if POST value is set, save it as '1', else '0'
		$is_locked = isset($_POST['mpp_is_locked']) ? '1' : '0';
		update_post_meta($post_id, 'mpp_is_locked', $is_locked);

		// Price
		if ($is_locked === '1' && isset($_POST['mpp_price']) && intval($_POST['mpp_price']) > 0) {
			update_post_meta($post_id, 'mpp_price', intval($_POST['mpp_price']));
		} else {
			// removes the price meta if content is unlocked or price is invalid (<= 0)
			delete_post_meta($post_id, 'mpp_price');
		}
	}
}
