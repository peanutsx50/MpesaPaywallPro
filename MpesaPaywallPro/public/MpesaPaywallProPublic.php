<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/public
 * @author     Your Name <email@example.com>
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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
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
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, MPP_URL . 'public/css/public-paywall.css', array(), (float) $this->version, 'all');
		wp_enqueue_style($this->plugin_name . '-modal', MPP_URL . 'public/css/phone-number-modal.css', array(), (float) $this->version, 'all');
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
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, MPP_URL . 'public/js/phone-number-modal.js', array('jquery'), false, false);
		wp_enqueue_script($this->plugin_name . '-payment', MPP_URL . 'public/js/process-payment.js', array('jquery'), false, false);
	}

	public function localize_scripts()
	{
		wp_localize_script(
			$this->plugin_name,
			'mpp_ajax_object',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('mpp_ajax_nonce'),
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
		if (is_admin() || !is_single()) {
			return $content;
		}
		// Get the current post ID
		$post_id = get_the_ID();

		// Retrieve paywall meta data
		$is_locked = get_post_meta($post_id, 'mpp_is_locked', true);
		$price = get_post_meta($post_id, 'mpp_price', true);

		// If content is not locked, return original content
		if ($is_locked !== '1') {
			return $content;
		}

		// Check if user has already paid (you'll implement this based on your payment logic)
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
	 * Generates a preview of the post content (first 100 words).
	 *
	 * Extracts the first 100 words from the post content, strips HTML tags,
	 * and wraps it in a container with a fade effect. This preview is displayed
	 * to users who haven't purchased access to the full content.
	 *
	 * @since      1.0.0
	 * @param      string    $content    The original post content.
	 * @return     string              HTML-formatted preview content with fade effect.
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

	// Dummy function to check if user has access
	public function user_has_access($post_id)
	{
		// Implement your logic to check if the user has paid for access
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
	public function process_payment()
	{
		//check nonce for security
		if (!isset($_POST['mpp_nonce']) || !wp_verify_nonce($_POST['mpp_nonce'], 'mpp_ajax_nonce')) {
			wp_send_json_error(['message' => 'Invalid request']); // deny request if nonce is invalid
			wp_die();
		}
		// get phone number, amount and post id from ajax request
		$phone_number = sanitize_text_field($_POST['phone_number']);

		// instantiate mpesa class and send payment request
		$mpesa = new MpesaPaywallProMpesa();
		$response = $mpesa->send_stk_push_request($phone_number);

		//handle response
		if ($response['status'] === 'success') {
			wp_send_json_success(['message' => 'Payment initiated. Please complete the payment on your phone.', 'checkout_request_id' => $response['checkout_request_id']]);
		} else {
			wp_send_json_error(['message' => 'Payment initiation failed: ' . $response['message']]);
		}
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
		register_rest_route('mppmpesa/v1', '/callback', [
			'methods' => ['POST', 'GET'],
			'callback' => [],
			'permission_callback' => '__return_true',
		]);
	}
}
