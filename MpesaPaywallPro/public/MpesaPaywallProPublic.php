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

use MpesaPaywallPro\core\MpesaPaywallProMpesa;

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

		wp_enqueue_style($this->mpesapaywallpro, MPP_URL . 'public/css/public-paywall.css', array(), (float) $this->version, 'all');
		wp_enqueue_style($this->mpesapaywallpro . '-modal', MPP_URL . 'public/css/phone-number-modal.css', array(), (float) $this->version, 'all');
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

		wp_enqueue_script($this->mpesapaywallpro, MPP_URL . 'public/js/phone-number-modal.js', array('jquery'), (float) $this->version, true);
		wp_enqueue_script($this->mpesapaywallpro . '-payment', MPP_URL . 'public/js/initiate-payment.js', array('jquery'), (float) $this->version, true);
		wp_enqueue_script($this->mpesapaywallpro . '-status', MPP_URL . 'public/js/check-payment-status.js', array('jquery'), (float) $this->version, true);
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
				'nonce'    => wp_create_nonce('mpp_ajax_nonce'),
				'callback_url' => rest_url('mpesapaywallpro/v1/callback'),
				'process_payment_url' => rest_url('mpesapaywallpro/v1/process-payment'),
				'access_expiry' => get_option('mpesapaywallpro_options')['payment_expiry'] ?? 30,
				'post_id' => $post_id,
				'amount' => $post_id ? $this->get_amount($post_id) : 0,
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
		if ($amount <= 0) {
			return $content;
		}

		// Check if user has already paid
		if ($this->user_has_access($post_id)) {
			return $content;
		}

		// Generate preview content
		$preview_content = $this->generate_preview($content);

		// Display preview html and attach paywall html
		$paywall_html = $preview_content . $this->render_paywall();
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
		$excerpt = get_option('mpesapaywallpro_options')['excerpt_length'] ?? 100;
		$preview_words = array_slice($words, 0, $excerpt);
		$preview_content = implode(' ', $preview_words);
		$preview_content .= '...<div class="mpp-preview-fade"></div>';
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
		$allowed_roles = get_option('mpesapaywallpro_options')['allowed_user_roles'] ?? ['administrator'];
		foreach ($current_user->roles as $role) {
			if (in_array($role, (array)$allowed_roles)) {
				return true;
			}
		}

		// Check for payment cookie (for immediate access after payment)
		$cookie_name = 'mpp_paid_' . $post_id;
		if (isset($_COOKIE[$cookie_name])) {
			/** @disregard */
			$checkout_id = sanitize_text_field($_COOKIE[$cookie_name]);
			// get post meta to verify
			$posts = get_posts([
				'post_type'   => 'mpesa',
				'meta_key'    => 'checkout_id',
				'meta_value'  => $checkout_id,
				'numberposts' => 1,
			]);

			if (!empty($posts)) {
				return true;
			}
		}

		return false;
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
	public function render_paywall()
	{
		ob_start();
		require_once MPP_PATH . 'public/partials/paywall-display.php';
		return ob_get_clean();
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

		$params = $request->get_json_params();
		$phone_number = sanitize_text_field($params['phone_number'] ?? '');
		$amount = absint($params['amount'] ?? 0);
		$nonce = sanitize_text_field($params['nonce'] ?? '');

		// Verify nonce
		if (!wp_verify_nonce($nonce, 'mpp_ajax_nonce')) {
			return new \WP_REST_Response([
				'success' => false,
				'data' => ['message' => 'Invalid request']
			], 403);
		}

		// Validate required fields
		if (empty($phone_number) || $amount < 1 || $amount > 150000) {
			return new \WP_REST_Response([
				'success' => false,
				'data' => ['message' => __('Invalid phone number or amount.', 'mpesapaywallpro')]
			], 400);
		}

		// Process payment
		$mpesa = new MpesaPaywallProMpesa();
		$response = $mpesa->send_stk_push_request($phone_number, $amount);

		if ($response['status'] === 'success') {
			$checkout_request_id = $response['response']['CheckoutRequestID'] ?? null;

			return new \WP_REST_Response([
				'success' => true,
				'data' => [
					'message' => 'Payment initiated. Please complete the payment on your phone.',
					'checkout_request_id' => $checkout_request_id,
				]
			], 200);
		} else {
			return new \WP_REST_Response([
				'success' => false,
				'data' => ['message' => 'Payment initiation failed: ' . ($response['message'] ?? 'Unknown error')]
			], 500);
		}
	}

	private function get_amount($post_id)
	{
		// Get settings and meta
		$options = get_option('mpesapaywallpro_options', []);
		$default_amount = absint($options['default_amount'] ?? 20);
		$auto_lock = !empty($options['auto_lock']);

		$is_locked = get_post_meta($post_id, 'mpp_is_locked', true) === '1';
		$custom_price = get_post_meta($post_id, 'mpp_price', true);
		$custom_price = is_numeric($custom_price) ? absint($custom_price) : 0;

		// Auto-lock enabled: use custom price if available, otherwise default
		if ($auto_lock) {
			return ($is_locked && $custom_price > 0) ? $custom_price : $default_amount;
		}

		// Auto-lock disabled: only charge if manually locked
		if ($is_locked) {
			return $custom_price > 0 ? $custom_price : $default_amount;
		}

		// Not locked
		return 0;
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
		$mpesa = new MpesaPaywallProMpesa();
		register_rest_route('mpesapaywallpro/v1', '/callback', [
			'methods' => ['POST', 'GET'],
			'callback' => [$mpesa, 'handle_callback'],
			'permission_callback' => '__return_true',
		]);

		register_rest_route('mpesapaywallpro/v1', '/process-payment', [
			'methods' => 'POST',
			'callback' => [$this, 'process_payment'],
			'permission_callback' => '__return_true',
		]);
	}
}
