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
        // Retrieve M-Pesa API credentials from WordPress options
        $this->consumer_key            = get_option('mpesapaywallpro_options')['consumer_key'] ?? '';
        $this->consumer_secret         = get_option('mpesapaywallpro_options')['consumer_secret'] ?? '';
        $this->shortcode               = get_option('mpesapaywallpro_options')['shortcode'] ?? '';
        $this->passkey                 = get_option('mpesapaywallpro_options')['passkey'] ?? '';
        $this->environment             = get_option('mpesapaywallpro_options')['env'] ?? 'sandbox';
        $this->account_reference       = get_option('mpesapaywallpro_options')['account_reference'] ?? '';
        $this->transaction_description = get_option('mpesapaywallpro_options')['transaction_description'] ?? '';

        // Generate OAuth access token for M-Pesa API authentication
        $this->access_token = $this->generate_access_token();

        // Create timestamp in YYYYMMDDHHmmss format for password generation
        $this->timestamp    = date('YmdHis');

        // Generate base64-encoded password for STK push authentication
        $this->password     = $this->generate_password();

        // Set the callback URL where M-Pesa will send payment confirmation webhooks
        $this->callbackurl  = home_url('/wp-json/mpesapaywallpro/v1/callback', 'https');

        // Set the appropriate M-Pesa API endpoint URL based on environment
        $this->url          = $this->environment === 'production' ?
            'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest' :
            'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        
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

        // Store the transaction amount for use in the callback handler
        $this->amount = $amount;

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
                'timeout' => 60,
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
        $auth_url = $this->environment === 'production' ?
            'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' :
            'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $credentials = base64_encode($this->consumer_key . ':' . $this->consumer_secret);

        // moving to wp_remote_get to fix vulnerable code
        $response = wp_remote_get($auth_url, [
            'headers' => [
                'Authorization' => 'Basic ' . $credentials,
            ],
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            MpesaPaywallProLogger::error("Failed to generate access token due to HTTP error: " . $response->get_error_message());
            return [
                'status' => 'error',
                'message' => 'HTTP Request failed: ' . $response->get_error_message(),
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        return isset($result['access_token']) ? $result['access_token'] : '';
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
        // check safaricom ip
        $client_ip = $this->get_client_ip();
        if (!$this->is_safaricom_ip($client_ip)) {
            MpesaPaywallProLogger::warning("Possible hack attempt: Received callback from unauthorized IP $client_ip. Callback ignored.");
            return rest_ensure_response([
                'status'  => 'error',
                'message' => 'Unauthorized IP address',
            ], 403);
        }

        // Ensure the request method is POST
        if ($request->get_method() !== 'POST') {
            return rest_ensure_response([
                'status'  => 'error',
                'message' => 'Invalid request method',
            ]);
        }

        $raw_body = $request->get_body();
        $body = json_decode($raw_body, true);

        $stk = $body['Body']['stkCallback'] ?? null;

        if (!$stk) {
            return rest_ensure_response(['status' => 'ignored']);
        }

        return $this->store_details_meta($stk);
    }

    private function get_client_ip()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwarded_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($forwarded_ips[0]);
        }

        return sanitize_text_field($ip);
    }

    private function safaricom_ips()
    {
        return [
            '196.201.212.0/22',   // Covers most Safaricom IPs
            '196.201.214.0/24',
        ];
    }

    private function is_safaricom_ip($ip)
    {
        $safaricom_ips = $this->safaricom_ips();
        foreach ($safaricom_ips as $range) {
            if ($this->ip_in_range($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    private function ip_in_range($ip, $range)
    {
        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        return ($ip & $mask) === $subnet;
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
            MpesaPaywallProLogger::error("Missing CheckoutRequestID in M-Pesa callback data. Callback cannot be processed.");
            return rest_ensure_response(['status' => 'error', 'message' => 'Missing checkout ID'], 400);
        }

        // Extract transaction metadata (only present on successful transactions)
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

        /*
     * Prevent duplicates (Safaricom retries callbacks)
     */
        $existing = get_posts([
            'post_type' => 'mpesa',
            'meta_query' => [
                [
                    'key' => 'checkout_id',
                    'value' => $checkoutId,
                    'compare' => '='
                ]
            ],
            'fields' => 'ids',
            'numberposts' => 1,
        ]);

        if ($existing) {
            $post_id = $existing[0];
            MpesaPaywallProLogger::info("Duplicate callback ignored for CheckoutRequestID: $checkoutId");
        } else {
            $post_id = wp_insert_post([
                'post_type'   => 'mpesa',
                'post_status' => 'publish',
                'post_title'  => 'Mpesa STK ' . $checkoutId,
            ], true); // Return WP_Error on failure
        }

        if (is_wp_error($post_id)) {
            MpesaPaywallProLogger::error('Mpesa: Failed to create post - ' . $post_id->get_error_message());
            return rest_ensure_response(['status' => 'error', 'message' => 'Database error'], 500);
        }

        // Store relevant data in post meta
        update_post_meta($post_id, 'checkout_id', $checkoutId);
        update_post_meta($post_id, 'merchant_request_id', $merchantRequestId);
        update_post_meta($post_id, 'status', $status);
        update_post_meta($post_id, 'result_code', $resultCode);
        update_post_meta($post_id, 'result_desc', $resultDesc);

        // Store transaction details (only available on successful transactions)
        if ($resultCode === 0) {
            update_post_meta($post_id, 'amount', $amount);
            update_post_meta($post_id, 'mpesa_receipt_number', $mpesaReceipt);
            update_post_meta($post_id, 'phone_number', $phoneNumber);
            update_post_meta($post_id, 'transaction_date', $transactionDate);
        }

        update_post_meta($post_id, 'date', current_time('mysql'));
        
        MpesaPaywallProLogger::info("Mpesa callback processed for CheckoutRequestID: $checkoutId with status: $status. Post ID: $post_id");
        return rest_ensure_response(['status' => 'ok', 'post_id' => $post_id], 200);
    }
}
