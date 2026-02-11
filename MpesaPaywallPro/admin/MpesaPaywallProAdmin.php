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
 * @author     SurgeTech <admin@surgetech.co.ke>
 */

namespace MpesaPaywallPro\admin;

use MpesaPaywallPro\core\MpesaPaywallProLogger;
use MpesaPaywallPro\core\MpesaPaywallProMpesa;

class MpesaPaywallProAdmin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $mpesapaywallpro    The ID of this plugin.
	 */
	private $mpesapaywallpro;

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
	 * @param      string    $mpesapaywallpro       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($mpesapaywallpro, $version)
	{

		$this->mpesapaywallpro = $mpesapaywallpro;
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
		 * defined in mpesapaywallpro_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The mpesapaywallpro_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->mpesapaywallpro, MPP_URL . 'admin/css/admin-settings.css', array(), $this->version, 'all');
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
		 * defined in mpesapaywallpro_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The mpesapaywallpro_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->mpesapaywallpro, MPP_URL . 'admin/js/admin-settings.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->mpesapaywallpro . '-meta-box', MPP_URL . 'admin/js/content-locked-meta-box.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->mpesapaywallpro . '-test-connection', MPP_URL . 'admin/js/test-connection.js', array('jquery'), $this->version, false);
	}

	public function localize_scripts()
	{
		wp_localize_script(
			$this->mpesapaywallpro,
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

	public function check_ssl()
	{
		if (!is_ssl()) {
			MpesaPaywallProLogger::warning("Site is running without SSL - M-Pesa transactions may be insecure.");
			add_action('admin_notices', function () {
?>
				<div class="notice notice-error is-dismissible">
					<p style="color: black;"><?php esc_html_e('Warning: Your site is not using SSL. For secure M-Pesa transactions, please enable HTTPS on your website.', 'mpesapaywallpro'); ?></p>
				</div>
<?php
			});
		}
	}

	/**
	 * Display the admin page content.
	 *
	 * @since    1.0.0
	 */
	public function display_admin_page()
	{
		MpesaPaywallProLogger::info("Admin settings page accessed by user: " . wp_get_current_user()->user_login);
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
			!wp_verify_nonce(wp_unslash($_POST['mpp_paywall_nonce']), 'mpp_save_paywall_meta')
		) {
			MpesaPaywallProLogger::warning("Possible CSRF attempt. Nonce verification failed when saving paywall meta box data for post ID: $post_id.");
			return;
		}

		// Check for autosave, prevent data from being saved during autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		// Check user permissions, if user can't edit post, exit
		if (!current_user_can('edit_post', $post_id)) {
			MpesaPaywallProLogger::warning("Unauthorized attempt to save paywall meta box data for post ID: $post_id by user ID: " . get_current_user_id());
			return;
		}

		// Lock status, if POST value is set, save it as '1', else '0'
		$is_locked = isset($_POST['mpp_is_locked']) ? '1' : '0';
		update_post_meta($post_id, 'mpp_is_locked', $is_locked);

		// Price
		if ($is_locked === '1' && isset($_POST['mpp_price']) && intval($_POST['mpp_price']) > 0) {
			update_post_meta($post_id, 'mpp_price', intval($_POST['mpp_price']));
			MpesaPaywallProLogger::info("Saved paywall meta box data for post ID: $post_id. Locked: $is_locked, Price: " . intval($_POST['mpp_price']));
		} else {
			// removes the price meta if content is unlocked or price is invalid (<= 0)
			delete_post_meta($post_id, 'mpp_price');
			MpesaPaywallProLogger::info("Saved paywall meta box data for post ID: $post_id. Locked: $is_locked. Price meta removed due to unlocked status or invalid price.");
		}
	}

	//test api connection
	public function test_connection()
	{
		// 1. Check for SSL first
		if (!is_ssl()) {
			MpesaPaywallProLogger::warning("Test connection failed due to lack of SSL. SSL is required for secure M-Pesa transactions.");
			wp_send_json_error([
				'message' => 'SSL is not enabled on your site. Please enable HTTPS to ensure secure M-Pesa transactions.'
			], 400);
		}

		// 2. Check nonce/security using check_ajax_referer
		if (!check_ajax_referer('mpp_admin_ajax_nonce', 'mpp_nonce', false)) {
			MpesaPaywallProLogger::warning("Test connection failed due to invalid nonce. Possible CSRF attempt.");
			wp_send_json_error(['message' => 'Invalid request'], 403);
		}

		// 3. Check user capability
		if (!current_user_can('manage_options')) {
			MpesaPaywallProLogger::warning("Test connection failed due to insufficient permissions.");
			wp_send_json_error(['message' => 'Unauthorized'], 403);
		}

		// 4. Get phone number and amount from $_POST
		$phone_number = isset($_POST['phone_number']) ? sanitize_text_field(wp_unslash($_POST['phone_number'])) : '';
		$amount = isset($_POST['amount']) ? absint($_POST['amount']) : 0;

		// 5. Validate required fields
		if (empty($phone_number) || $amount < MPESA_MIN || $amount > MPESA_MAX) {
			MpesaPaywallProLogger::warning("Test connection failed due to invalid input. Phone number: $phone_number, Amount: $amount.");
			wp_send_json_error(['message' => 'Invalid phone number or amount'], 400);
		}

		// 6. Instantiate mpesa class and send payment request
		$mpesa = new MpesaPaywallProMpesa();
		$response = $mpesa->send_stk_push_request($phone_number, $amount);

		// 7. Handle response
		if (isset($response['status']) && $response['status'] === 'success') {
			MpesaPaywallProLogger::info("Test payment initiated successfully for phone number: $phone_number with amount: $amount");
			wp_send_json_success([
				'message' => 'Payment initiated. Please check payment prompt on your phone.'
			]);
		} else {
			MpesaPaywallProLogger::error("Test payment initiation failed for phone number: $phone_number. Error: " . ($response['message'] ?? 'Unknown error'));
			wp_send_json_error([
				'message' => 'Payment initiation failed: ' . ($response['message'] ?? 'Unknown error')
			], 400);
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
			MpesaPaywallProLogger::warning("Unauthorized attempt to save settings by user ID: " . get_current_user_id());
			wp_die('Unauthorized');
		}

		MpesaPaywallProLogger::info("Saving settings for MpesaPaywallPro by user ID: " . get_current_user_id());

		// delete cached access token when saving settings to ensure new credentials are used
		delete_transient('mpp_access_token');

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
						'license_key'      => sanitize_text_field($options['license_key'] ?? ''),
						'auto_lock'        => absint($options['auto_lock'] ?? 0),
						'default_amount'   => absint($options['default_amount'] ?? 20),
						'button_color'     => sanitize_hex_color($options['button_color'] ?? '#0073aa'),
						'excerpt_length'   => absint($options['excerpt_length'] ?? 100),
						'paywall_message'  => wp_kses_post($options['paywall_message'] ?? ''),
						'payment_expiry'   => absint($options['payment_expiry'] ?? 30),

						// Access Control Settings
						'allowed_user_roles'   => array_map('sanitize_text_field', (array) ($options['allowed_user_roles'] ?? ['administrator'])),
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
					'license_key'      => '',
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


	/**
	 * Callback for scheduled log cleanup
	 * 
	 * @return void
	 */
	public static function cleanup_old_logs_callback()
	{
		$results = MpesaPaywallProLogger::clear_old_logs(MPP_LOG_EXPIRY_DAYS);

		// Optionally log the cleanup results
		if ($results['deleted_count'] > 0) {
			MpesaPaywallProLogger::info(
				'Automatic log cleanup completed. Deleted ' . $results['deleted_count'] . ' old log files.',
				['deleted_files' => $results['deleted_files'], 'errors' => $results['errors']]
			);
		}
	}
}
