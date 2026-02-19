<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://surgetech.co.ke
 * @since      1.0.0
 *
 * @package    mpesapaywallpro
 * @subpackage mpesapaywallpro/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    mpesapaywallpro
 * @subpackage mpesapaywallpro/public
 * @author     SurgeTech <admin@surgetech.co.ke>
 */

namespace MpesaPaywallPro\public;

use MpesaPaywallPro\core\MpesaPaywallProLogger;
use MpesaPaywallPro\core\MpesaPaywallProMpesa;
use MpesaPaywallPro\Core\MpesaPaywallProOptions;
use MpesaPaywallPro\core\MpesaPaywallProUtils;
use WP_Error;

// prevent direct access to the file
if (!defined('ABSPATH')) {
	exit;
}

// TODO: Need to implement cookie signing to avoid tampering
class MpesaPaywallProPublic
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
	 * @param      string    $mpesapaywallpro       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($mpesapaywallpro, $version)
	{

		$this->mpesapaywallpro = $mpesapaywallpro;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style($this->mpesapaywallpro, MPP_URL . 'public/css/public-paywall.css', array(), $this->version, 'all');
		wp_enqueue_style($this->mpesapaywallpro . '-modal', MPP_URL . 'public/css/phone-number-modal.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script($this->mpesapaywallpro, MPP_URL . 'public/js/dist/phone-number-modal.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script($this->mpesapaywallpro . '-payment', MPP_URL . 'public/js/dist/initiate-payment.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script($this->mpesapaywallpro . '-status', MPP_URL . 'public/js/dist/check-payment-status.min.js', array('jquery'), $this->version, true);
	}

	/**
	 * Registers REST API endpoint for M-Pesa payment callbacks.
	 *
	 * Registers a custom REST route that handles M-Pesa payment verification callbacks.
	 * The endpoint is accessible at /wp-json/mppmpesa/v1/callback and accepts both
	 * POST and GET requests. This endpoint allows the M-Pesa payment gateway to send
	 * payment status updates without authentication requirements.
	 *
	 * @since      1.0.0
	 * @return     void
	 */
	public function register_ajax_endpoints()
	{
		register_rest_route('mpesapaywallpro/v1', '/callback', [
			'methods' => ['POST'],
			'callback' => [MpesaPaywallProMpesa::class, 'handle_callback'],
			'permission_callback' => [$this, 'validate_safaricom_IP'],
			'show_in_index' => false, // Hide from REST API index for security through obscurity
			//'permission_callback' => '__return_true', // Testing
			'args'                => [
				'mpp_auth' => [
					'required' => true,
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		]);

		register_rest_route('mpesapaywallpro/v1', '/process-payment', [
			'methods' => 'POST',
			'callback' => [$this, 'process_payment'],
			'permission_callback' => [$this, 'validate_request'],
			'args' => [
				'phone_number' => [
					'required'          => true,
					'type'              => 'string',
					'validate_callback' => [$this, 'validate_phone_number'], // check if phone number is valid for M-Pesa
					'sanitize_callback' => 'sanitize_text_field',
				],
				'post_id' => [
					'required'          => true,
					'type'              => 'integer',
					'validate_callback' => [$this, 'validate_post_id'], // check if post ID is valid
					'sanitize_callback' => 'absint',
				]
			],
		]);

		register_rest_route('mpesapaywallpro/v1', '/confirm-payment', [
			'methods' => 'POST',
			'callback' => [$this, 'confirm_payment'],
			'permission_callback' => [$this, 'validate_request'],
			'args'                => [
				'checkout_id' => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'locked_post_id' => [
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				],
			]
		]);
	}

	/**
	 * Localizes payment data for JavaScript.
	 *
	 * Prepares and passes payment-related data to frontend JavaScript via wp_localize_script.
	 * Determines the payment amount by checking if the current post is locked with a custom price,
	 * otherwise uses the default amount from paywall settings. Includes AJAX endpoints, nonce for
	 * security verification, and payment timeout configuration.
	 *
	 * The localized data is made available to JavaScript via the `mpp_ajax_object` global variable.
	 *
	 * @since      1.0.0
	 * @return     void    Localizes script data for frontend JavaScript
	 */
	public function localize_scripts()
	{
		$post_id = get_the_ID();
		wp_localize_script(
			$this->mpesapaywallpro,
			'mpp_ajax_object',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('wp_rest'),
				'process_payment_url' => rest_url('mpesapaywallpro/v1/process-payment'),
				'confirm_payment_url' => rest_url('mpesapaywallpro/v1/confirm-payment'),
				'post_id' => $post_id, // locked post ID int or false
				'pollInterval' => 500, // 500 milisecs
				'maxPollAttempts' => 30, // total 1 minute of polling
				'phone_pattern' => '/^254(7(?:[0129][0-9]|4[0-3568]|5[7-9]|6[89])|11[0-5])\d{6}$/;'
			)
		);
	}

	/**
	 * Filters post content to enforce paywall restrictions.
	 *
	 * This function intercepts the post content on single post pages and applies
	 * paywall logic. If the post is locked and the user hasn't purchased access,
	 * it replaces the full content with a preview and paywall HTML. Only applies
	 * on the frontend for single post pages.
	 *
	 * @since      1.0.0
	 * @param      string    $content    The original post content.
	 * @return     string              The filtered content (either original content,
	 *                                 preview + paywall, or unchanged content).
	 */
	public function filter_post_content($content)
	{
		// Only apply on single post pages, not in admin or excerpts
		if (is_admin() || !is_single() || is_feed()) {
			return $content;
		}

		$post_id = get_the_ID();

		// Early return if no valid post ID
		if (!$post_id) {
			return $content;
		}

		// check if amount is 0, if so return content
		$amount = $this->get_amount($post_id);
		if ($amount < MPESA_MIN || $amount > MPESA_MAX) {
			return $content;
		}

		// Check if user has already paid
		if ($this->user_has_access($post_id)) {
			return $content;
		}

		MpesaPaywallProLogger::info("User does not have access to post ID: $post_id. Displaying paywall.");
		// Generate preview content
		$preview_content = $this->generate_preview($content);

		// Display preview html and attach paywall html and pass amount (should never be 0 or greater than max)
		$paywall_html = $preview_content . $this->render_paywall($amount);
		return $paywall_html;
	}

	/**
	 * Generates a preview of the post content based on configured excerpt length.
	 *
	 * Extracts the first N words from the post content (where N is configured via
	 * the 'excerpt_length' setting in paywall options, defaults to 100 words), strips
	 * HTML tags, adds ellipsis, and wraps it in a container with a fade effect. This
	 * preview is displayed to users who haven't purchased access to the full content.
	 *
	 * The excerpt_length is configurable in the plugin settings, allowing administrators
	 * to control how much content is previewed before the paywall is displayed.
	 *
	 * @since      1.0.0
	 * @param      string    $content    The original post content.
	 * @return     string              HTML-formatted preview content with ellipsis and fade effect.
	 *
	 * @uses       get_option() To retrieve the configured excerpt_length setting
	 * @uses       wp_strip_all_tags() To remove HTML tags from content
	 * @uses       wpautop() To convert line breaks to paragraph tags
	 */
	public function generate_preview($content)
	{
		$words = explode(' ', wp_strip_all_tags($content));
		$excerpt = (int) MpesaPaywallProOptions::get_options('excerpt_length', 100);
		$preview_words = array_slice($words, 0, $excerpt);
		$preview_content = implode(' ', $preview_words);
		//$preview_content .= '...<div class="mpp-preview-fade"></div>';
		return '<div class="mpp-content-preview">' . wpautop($preview_content) . '</div>';
	}

	/**
	 * Determines if the current user has access to locked content.
	 *
	 * Checks access eligibility through two mechanisms:
	 * 1. User role exemption - Users with roles in the 'allowed_user_roles' setting bypass the paywall
	 * 2. Payment verification - Validates payment status via a checkout ID cookie and corresponding M-Pesa post record
	 *
	 * Access is granted if either condition is met: the user has an exempt role OR they have a valid payment record.
	 *
	 * @since      1.0.0
	 * @param      int     $post_id    The post ID to check access for
	 * @return     bool                True if user has access, false otherwise
	 *
	 * @global array $_COOKIE           Payment cookie storage
	 *
	 * @uses       wp_get_current_user()    To retrieve the current user object
	 * @uses       get_option()             To fetch allowed user roles from plugin settings
	 * @uses       sanitize_text_field()    To safely sanitize the checkout ID from cookie
	 * @uses       get_posts()              To verify payment record in M-Pesa custom post type
	 */
	public function user_has_access($post_id)
	{
		// get current user role and check if exempted
		$current_user = wp_get_current_user();
		$allowed_roles = MpesaPaywallProOptions::get_options('allowed_user_roles', ['administrator']);
		foreach ($current_user->roles as $role) {
			if (in_array($role, (array)$allowed_roles)) {
				MpesaPaywallProLogger::info("User ID: {$current_user->ID} with role '{$role}' has access to post ID: $post_id due to role exemption.");
				return true;
			}
		}

		// For logged-in users: use Server side transients with expiry
		if (is_user_logged_in()) {
			$transient_key = 'mpp_access_' . $current_user->ID . '_' . $post_id;
			$checkout_id = get_transient($transient_key);

			if ($checkout_id) {
				return $this->verify_payment_record($checkout_id, $post_id);
			}
		}

		// For guests: Use WordPress cookies with nonces (signed, time-limited)
		if (isset($_COOKIE['mpp_paid_' . $post_id])) {
			return $this->verify_guest_payment_cookie($post_id);
		}

		return false;
	}

	/**
	 * Verify payment record exists and is successful
	 */
	private function verify_payment_record($checkout_id, $content_post_id)
	{
		$posts = get_posts([
			'post_type'   => 'mpesa',
			'post_status' => 'publish',
			'meta_query'  => [
				'relation' => 'AND',
				[
					'key'     => 'checkout_id',
					'value'   => $checkout_id,
					'compare' => '='
				],
				[
					'key'     => 'status',
					'value'   => 'success',
					'compare' => '='
				],
				[
					'key'     => 'content_post_id',
					'value'   => $content_post_id,
					'compare' => '='
				]
			],
			'numberposts' => 1,
			'fields'      => 'ids',
			'no_found_rows' => true,  // Performance: skip counting total rows
			'update_post_meta_cache' => false,  // Performance: skip meta cache
			'update_post_term_cache' => false,  // Performance: skip term cache
		]);

		if (empty($posts)) {
			MpesaPaywallProLogger::info("No payment record found for checkout ID: $checkout_id, post: $content_post_id");
			return false;
		}

		return true;
	}

	/**
	 * Verify guest payment cookie using WordPress nonces
	 */
	private function verify_guest_payment_cookie($post_id)
	{
		MpesaPaywallProLogger::info('Verifying guest payment cookie for post ID: ' . $post_id);

		$cookie_name = 'mpp_paid_' . $post_id;

		// check if cookie is set
		if (! isset($_COOKIE[$cookie_name])) {
			return false;
		}

		// sanitize cookie value to prevent tampering
		$cookie_value = sanitize_text_field(wp_unslash($_COOKIE[$cookie_name]));

		// Cookie format: checkout_id|nonce|expiry
		$parts = explode('|', $cookie_value);

		if (count($parts) !== 3) {
			return false;
		}

		// takes array and unpacks into variables based on order
		list($checkout_id, $nonce, $expiry) = $parts;

		// Check expiration
		if (time() > (int)$expiry) {
			MpesaPaywallProLogger::info("Payment cookie for post ID: $post_id has expired. Clearing cookie.");
			$this->clear_payment_cookie($post_id);
			return false;
		}

		// Verify nonce (uses WordPress salts internally)
		$expected_nonce = wp_hash($checkout_id . $post_id . $expiry, 'nonce');

		if (!hash_equals($expected_nonce, $nonce)) {
			MpesaPaywallProLogger::warning("Possible tampering detected. Payment cookie nonce verification failed for post ID: $post_id. Clearing cookie.");
			$this->clear_payment_cookie($post_id);
			return false;
		}

		// Verify against database
		return $this->verify_payment_record($checkout_id, $post_id);
	}


	/**
	 * Clear payment cookie
	 */
	private function clear_payment_cookie($post_id)
	{
		MpesaPaywallProLogger::info("Clearing payment cookie for post ID: $post_id");

		setcookie(
			'mpp_paid_' . $post_id,
			'',
			[
				'expires'  => time() - 3600,
				'path'     => '/',
				'secure'   => is_ssl(),
				'httponly' => true
			]
		);
	}

	/**
	 * Renders the paywall HTML markup.
	 *
	 * Includes and outputs the paywall display partial template, capturing
	 * the rendered output via output buffering. This includes the paywall UI,
	 * call-to-action, and payment form elements.
	 *
	 * @since      1.0.0
	 * @return     string    The rendered paywall HTML markup.
	 */
	public function render_paywall($mpp_price)
	{
		ob_start();
		$file_path = MPP_PATH . 'public/partials/paywall-display.php';
		require $file_path;
		return ob_get_clean();
	}

	public function validate_request($request)
	{
		//1. check for ssl and return error if not enabled
		if (!is_ssl()) {
			MpesaPaywallProLogger::error("Payment attempt blocked due to non-SSL connection.");
			return new WP_Error(
				'ssl_required',
				'SSL is not enabled on this site, transactions cannot be processed securely',
				['status' => 403]
			);
		}

		//2. verify nonce
		$nonce = $request->get_header('X-WP-Nonce');
		$raw_ip = $_SERVER['REMOTE_ADDR'] ?? '';
		$ip = filter_var($raw_ip, FILTER_VALIDATE_IP) ? sanitize_text_field($raw_ip) : 'UNKNOWN';
		if (!wp_verify_nonce($nonce, 'wp_rest')) {
			MpesaPaywallProLogger::warning("Invalid nonce during payment confirmation. Possible CSRF attempt from IP: $ip");
			return new WP_Error(
				'invalid_nonce',
				'Invalid request',
				['status' => 403]
			);
		}
		return true;
	}

	public function validate_phone_number($phone, $request, $key)
	{
		$results = MpesaPaywallProUtils::check_phone_number($phone);

		// Kenyan phone number validation
		if (!$results) {
			return new WP_Error(
				'invalid_phone',
				'Invalid phone number. Use format: 254XXXXXXXXX',
				['status' => 400]
			);
		}

		return true;
	}

	public function validate_safaricom_IP($request)
	{
		//check for ssl
		if (!is_ssl()) {
			MpesaPaywallProLogger::error("Unauthorized callback attempt from non-SSL connection.");
			return new WP_Error('ssl_required', 'SSL is required for this endpoint', ['status' => 403]);
		}

		$raw_ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
		$client_ip = filter_var($raw_ip, FILTER_VALIDATE_IP) ? $raw_ip : 'UNKNOWN';
		if (!MpesaPaywallProUtils::is_safaricom_ip($client_ip)) {
			MpesaPaywallProLogger::warning("Unauthorized callback attempt from IP: $client_ip. Access denied.");
			return new WP_Error('unauthorized_ip', 'Access denied', ['status' => 403]);
		}
		// validate auth token
		$url_token = $request->get_param('mpp_auth');

		// We use a hash of your NONCE_SALT to create a unique-to-you key
		$secret_key = wp_hash(wp_salt('nonce'), 'nonce');

		if (!hash_equals($secret_key, $url_token)) {
			MpesaPaywallProLogger::error("Unauthorized Callback: Token mismatch.");
			return new WP_Error('invalid_token', 'Access denied', ['status' => 403]);
		}

		return true;
	}

	public function validate_post_id($post_id, $request, $key)
	{
		if (!get_post($post_id)) {
			return new WP_Error(
				'invalid_post_id',
				'Invalid post ID',
				['status' => 400]
			);
		}
		return true;
	}

	/**
	 * Processes M-Pesa payment requests via AJAX.
	 *
	 * Handles incoming AJAX requests for M-Pesa payments. Validates the request using
	 * nonce verification for security, extracts the customer's phone number from POST data,
	 * and initiates an M-Pesa STK push payment request. Returns a JSON response indicating
	 * whether the payment was successfully initiated or failed.
	 *
	 * Expected POST Parameters:
	 * - mpp_nonce (string): Security nonce for verification
	 * - phone_number (string): Customer's M-Pesa registered phone number
	 *
	 * Response:
	 * - Success: Returns JSON with 'checkout_request_id' for payment tracking
	 * - Error: Returns JSON error message describing the failure reason
	 *
	 * @since      1.0.0
	 * @return     void    Sends JSON response and terminates execution
	 */
	public function process_payment(\WP_REST_Request $request)
	{
		// Get parameters already validated by REST API args
		$phone_number = $request->get_param('phone_number');
		$post_id	  = $request->get_param('post_id');

		// Get amount from server to process request
		$amount = $this->get_amount($post_id);

		if ($amount < MPESA_MIN || $amount > MPESA_MAX) {
			return new \WP_REST_Response([
				'success' => false,
				'data' => ['message' => 'Invalid amount for this content']
			], 400);
		}

		// Process payment
		$mpesa = new MpesaPaywallProMpesa();
		$response = $mpesa->send_stk_push_request($phone_number, $amount);
		$checkout_request_id = $response['response']['CheckoutRequestID'] ?? null;

		if ($response['status'] === 'success' && isset($checkout_request_id)) {
			$this->store_pending_transaction($checkout_request_id, $post_id);
			MpesaPaywallProLogger::info("Payment initiated successfully for phone number: $phone_number with amount: $amount. CheckoutRequestID: $checkout_request_id");
			return new \WP_REST_Response([
				'success' => true,
				'data' => [
					'message' => 'Payment initiated. Please complete the payment on your phone.',
					'checkout_request_id' => $checkout_request_id,
				]
			], 200);
		} else {
			MpesaPaywallProLogger::error("Payment initiation failed for phone number: $phone_number with amount: $amount.");
			return new \WP_REST_Response([
				'success' => false,
				'data' => ['message' => 'Payment initiation failed: ' . ($response['message'] ?? 'Unknown error')]
			], 500);
		}
	}

	private function get_amount($post_id)
	{
		//check if post ID is not empty invalid and return false
		if (!$post_id || !get_post($post_id)) {
			return false;
		}

		// Get settings and meta
		$options = MpesaPaywallProOptions::get_options();
		$default_amount = absint($options['default_amount'] ?? 20);
		$auto_lock = absint($options['auto_lock'] ?? 0) === 1;

		$is_locked = get_post_meta($post_id, 'mpp_is_locked', true) === '1';
		$custom_price = get_post_meta($post_id, 'mpp_price', true);
		$custom_price = is_numeric($custom_price) ? absint($custom_price) : 0;

		// Auto-lock enabled: use custom price if available, otherwise default
		if ($auto_lock) {
			return ($is_locked && $custom_price > 0) ? $custom_price : $default_amount;
		}

		// Auto-lock disabled: only charge if manually locked
		if ($is_locked) {
			return $custom_price > 0 ? $custom_price : 0;
		}

		// Not locked
		return 0;
	}

	private function store_pending_transaction($checkout_request_id, $post_id)
	{
		// store pending transaction in custom post type for later verification in callback
		$transaction = get_transient('mpp_pending_' . $checkout_request_id);
		if ($transaction !== false) {
			MpesaPaywallProLogger::warning("Pending transaction already exists for CheckoutRequestID: $checkout_request_id. Possible duplicate request.");
			return;
		}

		return set_transient('mpp_pending_' . $checkout_request_id, $post_id, 15 * MINUTE_IN_SECONDS); // 15 minutes timeout
	}

	public function confirm_payment($request)
	{
		// WordPress already sanitized these via args validation
		$checkoutId = $request->get_param('checkout_id');      // Already sanitized
		$content_post_id = $request->get_param('locked_post_id'); // Already absint()

		// Find the payment record
		global $wpdb;
		$post_id = $wpdb->get_var($wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} 
         WHERE meta_key = 'checkout_id' 
         AND meta_value = %s 
         LIMIT 1",
			$checkoutId
		));

		if (!$post_id) {
			return rest_ensure_response([
				'status'  => 'pending',
				'message' => 'Waiting for payment confirmation',
			]);
		}

		// Get payment status
		$status = get_post_meta($post_id, 'status', true);
		$result_desc = get_post_meta($post_id, 'result_desc', true);
		$mpesa_receipt = get_post_meta($post_id, 'mpesa_receipt_number', true);
		$paid_content_id = get_post_meta($post_id, 'content_post_id', true);

		// Verify the payment was made for this specific post
		if ((int)$paid_content_id !== (int)$content_post_id) {
			MpesaPaywallProLogger::warning(
				"Access bypass attempt: checkout_id $checkoutId was for post $paid_content_id, " .
					"but user requested access to post $content_post_id. IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown')
			);
			return rest_ensure_response([
				'status'  => 'failed',
				'message' => 'Payment was not made for this content',
			]);
		}

		// Handle failed payment
		if ($status === 'failed') {
			MpesaPaywallProLogger::error("Payment failed for checkout ID: $checkoutId. Reason: $result_desc");
			return rest_ensure_response([
				'status'      => 'failed',
				'message'     => $result_desc ?: 'Payment was cancelled or failed',
				'result_desc' => $result_desc,
			]);
		}

		// Handle successful payment
		if ($status === 'success') {
			$this->grant_payment_access($content_post_id, $checkoutId);
			MpesaPaywallProLogger::info("Access granted to post $content_post_id for checkout ID: $checkoutId");
			return rest_ensure_response([
				'status'          => 'success',
				'message'         => $result_desc ?: 'Payment successful',
				'mpesa_receipt'   => $mpesa_receipt,
				'result_desc'     => $result_desc,
				'content_post_id' => $content_post_id,
			]);
		}

		// Still pending
		return rest_ensure_response([
			'status'  => 'pending',
			'message' => 'Waiting for payment confirmation',
		]);
	}

	/**
	 * Set payment access after successful payment
	 * Call this from your callback handler after storing payment
	 */
	public function grant_payment_access($content_post_id, $checkout_id)
	{
		$expiry_days = MpesaPaywallProOptions::get_options('payment_expiry', 30);
		$duration = (int) $expiry_days * DAY_IN_SECONDS;

		if (is_user_logged_in()) {
			// Store in transient (server-side, auto-expires)
			$transient_key = 'mpp_access_' . get_current_user_id() . '_' . $content_post_id;
			set_transient($transient_key, $checkout_id, $duration);
		} else {
			// Set signed cookie for guests
			$this->set_payment_cookie($content_post_id, $checkout_id, $duration);
		}
	}

	/**
	 * Create signed cookie for guest users
	 */
	private function set_payment_cookie($content_post_id, $checkout_id, $duration)
	{
		// gets current time then add duration to set expiry
		$expiry = time() + $duration;

		// Create nonce using WordPress's wp_hash (uses WordPress salts)
		$nonce = wp_hash($checkout_id . $content_post_id . $expiry, 'nonce');

		// Cookie format: checkout_id|nonce|expiry
		$cookie_value = $checkout_id . '|' . $nonce . '|' . $expiry;

		setcookie(
			'mpp_paid_' . $content_post_id,
			$cookie_value,
			[
				'expires'  => $expiry,
				'path'     => '/',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax'
			]
		);
	}
}
