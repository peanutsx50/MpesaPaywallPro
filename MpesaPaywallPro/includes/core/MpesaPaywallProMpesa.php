<?php

/**
 * Mpesa Paywall Pro M-Pesa core class.
 * 
 * This class handles M-Pesa payment processing and integration
 * for the Mpesa Paywall Pro WordPress plugin.
 * 
 * @since    1.0.0
 * @package  MpesaPaywallPro
 * 
 * @wordpress-core
 * @subpackage MpesaPaywallPro/includes/core
 * 
 * 
 */

namespace MpesaPaywallPro\core;

use WP_REST_Response;

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

class MpesaPaywallProMpesa
{
    // Mpesa properties
    private $consumer_key;
    private $consumer_secret;
    private $shortcode;
    private $password;
    private $passkey;
    private $access_token;
    private $timestamp;
    private $environment; //sandbox or production
    private $callbackurl;
    private $account_reference;
    private $transaction_description;
    private $err;
    private $url;
    private $amount;
    private $transactionType = 'CustomerPayBillOnline';
    private const MPESA_PRODUCTION_URL = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    private const MPESA_SANDBOX_URL = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';



    /**
     * Initializes M-Pesa configuration and generates required authentication tokens.
     *
     * This private method is called before each M-Pesa API request to populate all necessary
     * configuration properties from WordPress options. It retrieves M-Pesa credentials
     * (consumer key, secret, shortcode, passkey), generates an OAuth access token for API
     * authentication, creates a timestamp and password for STK push requests, and sets up
     * the appropriate API endpoint URL based on the environment (sandbox or production).
     *
     * The method consolidates all initialization logic in one place to ensure consistent
     * state before making API calls. Configuration defaults are provided for optional fields,
     * while missing credentials will be caught during validation.
     *
     * @since      1.0.0
     * @access     private
     * @return     void    Sets instance properties for M-Pesa API communication
     *
     * @uses       get_option() To retrieve M-Pesa configuration from WordPress options
     * @uses       generate_access_token() To obtain OAuth token from M-Pesa API
     * @uses       generate_password() To create STK push password from shortcode, passkey, and timestamp
     * @uses       home_url() To construct the callback URL for payment notifications
     */
    private function run()
    {
        //Options retrieval
        $options = get_option('mpesapaywallpro_options', []);

        // Retrieve M-Pesa API credentials from WordPress options
        $this->consumer_key            = $options['consumer_key'] ?? '';
        $this->consumer_secret         = $options['consumer_secret'] ?? '';
        $this->shortcode               = $options['shortcode'] ?? '';
        $this->passkey                 = $options['passkey'] ?? '';
        $this->environment             = $options['env'] ?? 'sandbox';
        $this->account_reference       = $options['account_reference'] ?? '';
        $this->transaction_description = $options['transaction_description'] ?? '';

        //Access token generation
        $this->access_token = $this->generate_access_token();

        //Timestamp
        $this->timestamp = date('YmdHis');

        //Password generation
        $this->password = $this->generate_password();

        // Callback URL
        $this->callbackurl = home_url('/wp-json/mpesapaywallpro/v1/callback', 'https');

        // Set the appropriate M-Pesa API endpoint URL based on environment
        $this->url          = $this->environment === 'production' ?
            self::MPESA_PRODUCTION_URL :
            self::MPESA_SANDBOX_URL;


        MpesaPaywallProLogger::info("M-Pesa configuration initialized. Environment: {$this->environment}. API URL set to: {$this->url}");
    }

    /**
     * Initiates an M-Pesa STK push payment request.
     *
     * Sends a payment request to the M-Pesa API which prompts the customer to enter their
     * M-Pesa PIN on their phone. This method handles the complete flow: initializing M-Pesa
     * configuration, validating required settings, constructing the payment request payload,
     * communicating with the M-Pesa API, and handling the response.
     *
     * The method performs configuration validation to ensure all required M-Pesa credentials
     * are present before attempting the API call. It constructs a request payload with payment
     * details including the customer's phone number, transaction amount, business shortcode,
     * and callback URL for payment notifications.
     *
     * @since      1.0.0
     * @param      string    $phone_number    The customer's M-Pesa registered phone number (e.g., '254712345678')
     * @param      int       $amount          The transaction amount in KES to charge the customer
     * @return     array                      Response array with the following structure:
     *                                        - On success: [
     *                                            'status' => 'success',
     *                                            'message' => 'Payment request sent. Enter your M-Pesa PIN.',
     *                                            'response' => $decoded_response containing CheckoutRequestID
     *                                          ]
     *                                        - On validation error: [
     *                                            'status' => 'error',
     *                                            'message' => 'Missing required Mpesa configuration details for...',
     *                                            'data' => ['missing_field' => '...']
     *                                          ]
     *                                        - On API error: [
     *                                            'status' => 'error',
     *                                            'message' => 'M-Pesa error message',
     *                                            'error_code' => 'error code from M-Pesa',
     *                                            'response' => full M-Pesa API response
     *                                          ]
     *                                        - On exception: [
     *                                            'status' => 'error',
     *                                            'message' => 'Exception: error details'
     *                                          ]
     *
     * @uses       run() To initialize M-Pesa configuration and generate tokens
     * @uses       validate_config() To ensure all required M-Pesa credentials are present
     * @uses       curl_init() To initialize HTTP client for API communication
     * @uses       json_encode() To serialize the payment request payload
     * @uses       json_decode() To parse the M-Pesa API response
     */
    public function send_stk_push_request($phone_number, $amount)
    {
        // Initialize M-Pesa configuration properties and generate access token
        $this->run();

        // Validate that all required M-Pesa configuration fields are populated
        $validation_result = $this->validate_config();
        if ($validation_result['status'] === 'error') {
            MpesaPaywallProLogger::error("M-Pesa configuration validation failed: " . $validation_result['message']);
            return $validation_result;
        }

        try {
            // Construct the payment request payload for M-Pesa STK push API
            $data = [
                "BusinessShortCode" => $this->shortcode,
                "Password" => $this->password,
                "Timestamp" => $this->timestamp,
                "TransactionType" => $this->transactionType,
                "Amount" => $amount,
                "PartyA" => $phone_number,
                "PartyB" => $this->shortcode,
                "PhoneNumber" => $phone_number,
                "AccountReference" => $this->account_reference,
                "TransactionDesc" => $this->transaction_description,
                "CallBackURL" => $this->callbackurl,
            ];

            // moving to wp_remote_post to fix vulnerable code
            $response = wp_remote_post($this->url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token,
                    'Content-Type'  => 'application/json',
                ],
                'body'    => json_encode($data),
                'timeout' => 30, // Reduced from 60 - M-Pesa responds quickly
                'httpversion' => '1.1', // Force HTTP/1.1 for better compatibility
                'sslverify' => true, // Explicit for security
            ]);

            if (is_wp_error($response)) {
                return [
                    'status' => 'error',
                    'message' => 'HTTP Request failed: ' . $response->get_error_message(),
                ];
            }

            $body = wp_remote_retrieve_body($response);
            $decoded_response = json_decode($body, true);

            // Check if M-Pesa API returned an error code
            if (isset($decoded_response['errorCode'])) {
                return [
                    'status' => 'error',
                    'message' => $decoded_response['errorMessage'] ?? 'M-Pesa API Error',
                    'error_code' => $decoded_response['errorCode'],
                    'response' => $decoded_response
                ];
            }

            MpesaPaywallProLogger::info("STK push request sent successfully to M-Pesa API for phone number $phone_number with amount $amount.");

            // Return success response with payment details for client-side tracking
            return [
                'status' => 'success',
                'message' => 'Payment request sent. Enter your M-Pesa PIN.',
                'response' => $decoded_response,
            ];
        } catch (\Exception $e) {
            // Capture and return any thrown exceptions as error response
            MpesaPaywallProLogger::error("Exception during STK push request: " . $e->getMessage());
            $this->err = $e->getMessage();
            return [
                'status' => 'error',
                'message' => 'Exception: ' . $this->err
            ];
        }
    }

    // generate access token for mpesa api
    private function generate_access_token()
    {
        // Check for cache first (early return for best performance)
        $cached_token = get_transient('mpp_mpesa_access_token');
        if ($cached_token) {
            return $cached_token;
        }

        // Use class constants for URLs (defined once, reused always)
        static $auth_urls = [
            'production' => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'sandbox'    => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
        ];

        $auth_url = $auth_urls[$this->environment] ?? $auth_urls['sandbox'];

        // Pre-build authorization header (avoid string concatenation in array)
        $credentials = base64_encode($this->consumer_key . ':' . $this->consumer_secret);

        // Optimized HTTP request with minimal timeout
        $response = wp_remote_get($auth_url, [
            'headers' => [
                'Authorization' => 'Basic ' . $credentials,
            ],
            'timeout' => 30, // Reduced from 60 - M-Pesa typically responds in <5s
            'sslverify' => true, // Explicit for security
        ]);

        // Early error handling
        if (is_wp_error($response)) {
            $error_msg = $response->get_error_message();
            MpesaPaywallProLogger::error("Failed to generate access token: {$error_msg}");
            return '';
        }

        // Parse response
        $result = json_decode(wp_remote_retrieve_body($response), true);

        // Validate token exists
        if (empty($result['access_token'])) {
            MpesaPaywallProLogger::error("Access token missing in API response");
            return '';
        }

        $access_token = $result['access_token'];

        // Cache for 50 minutes
        set_transient('mpp_mpesa_access_token', $access_token, 50 * MINUTE_IN_SECONDS);

        return $access_token;
    }

    // generate password for stk push
    private function generate_password()
    {
        $data_to_encode = $this->shortcode . $this->passkey . $this->timestamp;
        $password = base64_encode($data_to_encode);
        return $password;
    }

    //validate all the field values are not empty
    private function validate_config()
    {
        $required_fields = ['consumer_key', 'consumer_secret', 'shortcode', 'passkey', 'account_reference', 'transaction_description'];

        foreach ($required_fields as $field) {
            if (empty($this->$field)) {
                return [
                    'status' => 'error',
                    'message' => 'Missing required Mpesa configuration details for ' . $field,
                    'data' => [
                        'missing_field' => $field,
                        'field_value' => $this->$field
                    ]
                ];
            }
        }
        return ['status' => 'success', 'message' => 'Mpesa configuration is valid'];
    }

    /**
     * Handles M-Pesa STK push callback webhook from Safaricom.
     *
     * Receives and processes payment callback notifications from the M-Pesa API
     * when a customer completes or cancels an STK push payment request. This webhook
     * handler validates the incoming request, extracts payment result details, and
     * stores the transaction information for later retrieval and processing.
     *
     * The method expects a POST request containing M-Pesa callback data in the following
     * structure:
     * {
     *   "Body": {
     *     "stkCallback": {
     *       "CheckoutRequestID": "...",
     *       "ResultCode": 0,
     *       "ResultDesc": "..."
     *     }
     *   }
     * }
     *
     * Result codes from M-Pesa:
     * - 0: Transaction successful
     * - Non-zero: Transaction failed or cancelled
     *
     * @since      1.0.0
     * @param      WP_REST_Request $request    The incoming webhook request from M-Pesa containing
     *                                        callback data with payment results
     * @return     WP_REST_Response           JSON response indicating callback processing status:
     *                                        - On ignored callback (missing stkCallback): ['status' => 'ignored']
     *                                        - On successful processing: ['status' => 'ok']
     *                                        - On error storing data: ['status' => 'error']
     *
     * @uses       WP_REST_Request::get_method() To verify the request method is POST
     * @uses       WP_REST_Request::get_body() To retrieve the raw JSON callback payload
     * @uses       json_decode() To parse the JSON callback data
     * @uses       sanitize_text_field() To sanitize CheckoutRequestID and ResultDesc
     * @uses       rest_ensure_response() To format the response as WP REST response
     * @uses       store_details_meta() To persist transaction data to the database
     */
    public function handle_callback($request)
    {
        // Ensure the request method is POST
        if ($request->get_method() !== 'POST') {
            return rest_ensure_response([
                'status'  => 'error',
                'message' => 'Invalid request method',
            ]);
        }

        // check if safaricom ip
        $client_ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN');
        error_log("Received M-Pesa callback from IP: $client_ip");
        if (!MpesaPaywallProUtils::is_safaricom_ip($client_ip)) {
            MpesaPaywallProLogger::warning("Possible hack attempt: Received callback from unauthorized IP $client_ip. Callback ignored.");
            return rest_ensure_response([
                'status'  => 'error',
                'message' => 'Unauthorized IP address',
            ], 403);
        }

        $raw_body = $request->get_body();
        $body = json_decode($raw_body, true);

        $stk = $body['Body']['stkCallback'] ?? null;

        if (!$stk) {
            return rest_ensure_response(['status' => 'ignored']);
        }

        return $this->store_details_meta($stk);
    }


    /**
     * Stores M-Pesa transaction details and callback metadata to the database.
     *
     * Persists M-Pesa payment callback information to a custom post type, handling both
     * initial transaction recording and duplicate prevention for callback retries. This method
     * creates or retrieves an existing 'mpesa' post record and stores all transaction metadata
     * including checkout ID, payment status, result codes, and timestamp for audit and
     * reconciliation purposes.
     *
     * The method prevents duplicate entries by checking if a transaction with the same
     * CheckoutRequestID already exists in the database. If found, it updates the existing
     * post; otherwise, it creates a new post record. All transaction details are stored
     * as post metadata for flexible querying and reporting.
     *
     * @since      1.0.0
     * @param      string    $checkoutId     The unique M-Pesa checkout request ID for transaction identification
     * @param      string    $status         The transaction status: 'success' or 'failed'
     * @param      int       $resultCode     The M-Pesa result code (0 = success, non-zero = failure)
     * @param      string    $resultDesc     The M-Pesa result description message
     * @return     WP_REST_Response        JSON response indicating storage operation status:
     *                                     - On success: ['status' => 'ok']
     *                                     - On error creating/retrieving post: ['status' => 'error']
     *
     * @uses       get_posts() To query existing 'mpesa' posts by checkout_id meta value
     * @uses       wp_insert_post() To create a new 'mpesa' post record for new transactions
     * @uses       is_wp_error() To validate post creation was successful
     * @uses       update_post_meta() To store transaction metadata fields
     * @uses       current_time() To generate the transaction processing timestamp
     * @uses       rest_ensure_response() To format the response as WP REST response
     */
    public function store_details_meta($stk)
    {
        // Extract basic callback data
        $checkoutId = sanitize_text_field($stk['CheckoutRequestID'] ?? '');
        $merchantRequestId = sanitize_text_field($stk['MerchantRequestID'] ?? '');
        $resultCode = (int) ($stk['ResultCode'] ?? -1);
        $resultDesc = sanitize_text_field($stk['ResultDesc'] ?? '');

        if (empty($checkoutId)) {
            MpesaPaywallProLogger::error("Missing CheckoutRequestID in M-Pesa callback data.");
            return rest_ensure_response(['status' => 'error', 'message' => 'Missing checkout ID'], 400);
        }

        // Extract transaction metadata
        $amount = 0;
        $phoneNumber = '';
        $mpesaReceipt = '';
        $transactionDate = '';

        if ($resultCode === 0 && isset($stk['CallbackMetadata']['Item'])) {
            foreach ($stk['CallbackMetadata']['Item'] as $item) {
                switch ($item['Name']) {
                    case 'Amount':
                        $amount = floatval($item['Value'] ?? 0);
                        break;
                    case 'MpesaReceiptNumber':
                        $mpesaReceipt = sanitize_text_field($item['Value'] ?? '');
                        break;
                    case 'PhoneNumber':
                        $phoneNumber = sanitize_text_field($item['Value'] ?? '');
                        break;
                    case 'TransactionDate':
                        $transactionDate = sanitize_text_field($item['Value'] ?? '');
                        break;
                }
            }
        }
        $status = ($resultCode === 0) ? 'success' : 'failed';

        // Duplicate check
        global $wpdb;
        $existing_post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
         WHERE meta_key = 'checkout_id' 
         AND meta_value = %s 
         LIMIT 1",
            $checkoutId
        ));

        if ($existing_post_id) {
            MpesaPaywallProLogger::info("Duplicate callback ignored for CheckoutRequestID: $checkoutId");
            return rest_ensure_response(['status' => 'ok', 'post_id' => $existing_post_id, 'duplicate' => true], 200);
        }

        // ULTRA-OPTIMIZED Single database transaction
        $current_time = current_time('mysql'); // get local time for post
        $gmt_time = get_gmt_from_date($current_time); // gets UTC time for consistency in storage

        // Start transaction for atomic insert
        $wpdb->query('START TRANSACTION');

        // Insert post
        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$wpdb->posts} 
        (post_type, post_status, post_title, post_date, post_date_gmt, post_modified, post_modified_gmt) 
        VALUES (%s, %s, %s, %s, %s, %s, %s)",
            'mpesa', // custom post type for M-Pesa transactions
            'publish', // publish immediately for visibility in admin
            'Mpesa STK ' . $checkoutId, // title with checkout ID for easy identification
            $current_time, // local time for post_date
            $gmt_time, // UTC time for post_date_gmt
            $current_time, // local time for post_modified
            $gmt_time // UTC time for post_modified_gmt
        ));

        // get id of newly inserted post
        $post_id = $wpdb->insert_id;

        if (!$post_id) {
            $wpdb->query('ROLLBACK');
            MpesaPaywallProLogger::error('Failed to insert post into database');
            return rest_ensure_response(['status' => 'error', 'message' => 'Database error'], 500);
        }

        // Build meta values
        $meta_rows = [
            [$post_id, 'checkout_id', $checkoutId],
            [$post_id, 'merchant_request_id', $merchantRequestId],
            [$post_id, 'status', $status],
            [$post_id, 'result_code', (string)$resultCode],
            [$post_id, 'result_desc', $resultDesc],
            [$post_id, 'date', $current_time],
        ];

        // add more meta if transaction was successful
        if ($resultCode === 0) {
            $meta_rows[] = [$post_id, 'amount', (string)$amount];
            $meta_rows[] = [$post_id, 'mpesa_receipt_number', $mpesaReceipt];
            $meta_rows[] = [$post_id, 'phone_number', $phoneNumber];
            $meta_rows[] = [$post_id, 'transaction_date', $transactionDate];
        }

        // Single bulk insert for all meta
        $placeholders = implode(', ', array_fill(0, count($meta_rows), '(%d, %s, %s)')); // build placeholders for prepared statement
        $values = [];
        foreach ($meta_rows as $row) {
            $values = array_merge($values, $row);
        }

        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES {$placeholders}",
            ...$values
        ));

        // Commit transaction
        $wpdb->query('COMMIT');

        // Clean cache
        clean_post_cache($post_id);

        MpesaPaywallProLogger::info("Callback processed for CheckoutRequestID: $checkoutId with status: $status. Post ID: $post_id");

        return rest_ensure_response(['status' => 'ok', 'post_id' => $post_id], 200);
    }
}
