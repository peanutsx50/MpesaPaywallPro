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
 * @author     Surge Technologies <admin@surgetech.co.ke>
 */

namespace MpesaPaywallPro\admin;

use MpesaPaywallPro\core\MpesaPaywallProMpesa;

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

		wp_enqueue_style($this->plugin_name, MPP_URL . 'admin/css/admin-settings.css', array(), false, 'all');
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

		wp_enqueue_script($this->plugin_name, MPP_URL . 'admin/js/admin-settings.js', array('jquery'), false, false);
		wp_enqueue_script($this->plugin_name . '-meta-box', MPP_URL . 'admin/js/content-locked-meta-box.js', array('jquery'), false, false);
		wp_enqueue_script($this->plugin_name . '-test-connection', MPP_URL . 'admin/js/test-connection.js', array('jquery'), false, false);
	}

	public function localize_scripts()
	{
		wp_localize_script(
			$this->plugin_name,
			'mpp_admin_ajax_object',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('mpp_admin_ajax_nonce'),
				'phone_number' => get_option('mpesapaywallpro_options')['test_phone_number'] ?? '',
			)
		);
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

	/**
	 * Save paywall meta box data for a post.
	 *
	 * Handles saving paywall configuration data submitted from the post editor meta box.
	 * This function implements security checks including nonce verification, autosave prevention,
	 * and user capability validation before processing and storing the paywall settings.
	 *
	 * Security & Validation:
	 * - Verifies nonce token to prevent cross-site request forgery (CSRF) attacks
	 * - Skips processing during WordPress autosave operations
	 * - Checks that the current user has permission to edit the post
	 *
	 * Data Processing:
	 * - Saves the lock status as '1' (locked) or '0' (unlocked)
	 * - Only saves price data if content is locked AND price is a valid positive integer
	 * - Removes price metadata if content is unlocked or price is invalid
	 *
	 * @since    1.0.0
	 * @param    int $post_id The ID of the post being saved
	 * @return   void
	 *
	 * @uses     wp_verify_nonce() To validate CSRF token
	 * @uses     current_user_can() To check user edit permissions
	 * @uses     update_post_meta() To save paywall settings
	 * @uses     delete_post_meta() To remove invalid price metadata
	 */
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

	//test api connection
	public function test_connection()
	{
		//check nonce for security
		if (!isset($_POST['mpp_nonce']) || !wp_verify_nonce($_POST['mpp_nonce'], 'mpp_admin_ajax_nonce')) {
			wp_send_json_error(['message' => 'Invalid request']); // deny request if nonce is invalid
			wp_die();
		}
		// get phone number and amount from ajax request
		$phone_number = sanitize_text_field($_POST['phone_number']);
		$amount = intval($_POST['amount']);

		// instantiate mpesa class and send payment request
		$mpesa = new MpesaPaywallProMpesa();
		$response = $mpesa->send_stk_push_request($phone_number, $amount);

		//handle response
		if ($response['status'] === 'success') {
			wp_send_json_success(['message' => 'Payment initiated. Please complete the payment on your phone.']);
		} else {
			wp_send_json_error(['message' => 'Payment initiation failed: ' . $response['message']]);
		}
	}

	/**
	 * Register plugin settings with WordPress Settings API.
	 *
	 * Registers the MpesaPaywallPro settings with the WordPress Settings API, including
	 * comprehensive data sanitization and validation. This method defines all configurable
	 * options for M-Pesa API integration, paywall behavior, and access control.
	 *
	 * Settings Groups:
	 * - M-Pesa API Settings: Authentication credentials and environment configuration
	 * - Paywall Settings: Content display and payment behavior options
	 * - Access Control Settings: User role permissions and payment constraints
	 *
	 * Sanitization:
	 * - Text fields: Uses sanitize_text_field() to remove HTML and malicious content
	 * - Integers: Uses absint() to ensure positive integer values
	 * - Colors: Uses sanitize_hex_color() to validate hex color codes
	 * - HTML content: Uses wp_kses_post() to allow safe HTML in paywall messages
	 * - Arrays: Uses array_map() with sanitize_text_field() for role arrays
	 *
	 * Security & Validation:
	 * - Environment: Restricted to 'production' or 'sandbox' values only
	 * - Boolean fields: Converted to 1 (true) or 0 (false)
	 * - Invalid or missing values: Falls back to sensible defaults
	 *
	 * @since    1.0.0
	 * @return   void
	 *
	 * @uses     register_setting() WordPress Settings API function
	 * @uses     sanitize_text_field() To clean text input
	 * @uses     absint() To validate positive integers
	 * @uses     sanitize_hex_color() To validate color hex codes
	 * @uses     wp_kses_post() To allow safe HTML in messages
	 */
	public function save_settings()
	{
		// NOW it's safe to save settings
		if (!current_user_can('manage_options')) {
			wp_die('Unauthorized');
		}
		
		register_setting(
			'mpesapaywallpro_settings_group',
			'mpesapaywallpro_options',
			[
				'type'              => 'array',
				'sanitize_callback' => function ($options) {
					$options = is_array($options) ? $options : [];

					// Get existing options from database
					$existing_options = get_option('mpesapaywallpro_options', []);

					// Merge new options with existing ones (new values override existing)
					$options = array_merge($existing_options, $options);

					return [
						// M-Pesa API Settings
						'consumer_key'            => sanitize_text_field($options['consumer_key'] ?? ''),
						'consumer_secret' 		  => sanitize_text_field($options['consumer_secret'] ?? ''),
						'shortcode'        		  => sanitize_text_field($options['shortcode'] ?? ''),
						'passkey'          		  => sanitize_text_field($options['passkey'] ?? ''),
						'account_reference' 	  => sanitize_text_field($options['account_reference'] ?? ''),
						'transaction_description' => sanitize_text_field($options['transaction_description'] ?? ''),
						'env'              		  => (isset($options['env']) && $options['env'] === 'production')
							? 'production'
							: 'sandbox',
						'test_phone_number'      => sanitize_text_field($options['test_phone_number'] ?? ''),

						// Paywall Settings
						'auto_lock'        => isset($options['auto_lock']) ? 1 : 0,
						'default_amount'   => absint($options['default_amount'] ?? 20),
						'button_color'     => sanitize_hex_color($options['button_color'] ?? '#0073aa'),
						'excerpt_length'   => absint($options['excerpt_length'] ?? 100),
						'paywall_message'  => wp_kses_post($options['paywall_message'] ?? ''),
						'payment_expiry'   => absint($options['payment_expiry'] ?? 30),

						// Access Control Settings
						'allowed_user_roles'   => array_map('sanitize_text_field', (array) ($options['allowed_user_roles'] ?? ['administrator'])),
						'enable_auto_unlock'   => isset($options['enable_auto_unlock']) ? 1 : 0,
						'payment_timeout'      => absint($options['payment_timeout'] ?? 300),
						'max_payment_attempts' => absint($options['max_payment_attempts'] ?? 3),
					];
				},
				'default' => [
					// M-Pesa API Settings
					'consumer_key'     => '',
					'consumer_secret'  => '',
					'shortcode'        => '',
					'passkey'          => '',
					'env'              => 'sandbox',

					// Paywall Settings
					'auto_lock'        => 0,
					'default_amount'   => 20,
					'button_color'     => '#0073aa',
					'excerpt_length'   => 100,
					'paywall_message'  => '',
					'payment_expiry'   => 30,

					// Access Control Settings
					'allowed_user_roles'   => ['administrator'],
				],
			]
		);
	}
}
