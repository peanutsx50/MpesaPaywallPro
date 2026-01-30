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

            if(is_wp_error($response)) {
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

            // Return success response with payment details for client-side tracking
            return [
                'status' => 'success',
                'message' => 'Payment request sent. Enter your M-Pesa PIN.',
                'response' => $decoded_response,
            ];
        } catch (\Exception $e) {
            // Capture and return any thrown exceptions as error response
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

        if(is_wp_error($response)) {
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

    // handle callback
    public function handle_callback($request)
    {

        /*
     * ======================================================
     * 1. SAFARICOM CALLBACK (POST)
     * ======================================================
     */
        if ($request->get_method() === 'POST') {

            $raw_body = $request->get_body();
            $body = json_decode($raw_body, true);

            $stk = $body['Body']['stkCallback'] ?? null;

            if (!$stk) {
                return rest_ensure_response(['status' => 'ignored']);
            }

            $checkoutId = sanitize_text_field($stk['CheckoutRequestID']);
            $resultCode = (int) $stk['ResultCode'];
            $resultDesc = sanitize_text_field($stk['ResultDesc'] ?? '');

            $status = ($resultCode === 0) ? 'success' : 'failed';

            /*
         * Prevent duplicates (Safaricom retries callbacks)
         */
            $existing = get_posts([
                'post_type'   => 'mpesa',
                'meta_key'    => 'checkout_id',
                'meta_value'  => $checkoutId,
                'fields'      => 'ids',
                'numberposts' => 1,
            ]);

            if ($existing) {
                $post_id = $existing[0]; // returns back the post id
            } else {
                $post_id = wp_insert_post([
                    'post_type'   => 'mpesa',
                    'post_status' => 'publish',
                    'post_title'  => 'Mpesa STK ' . $checkoutId,
                ]); // after create complete returns back the post id
            }

            if (is_wp_error($post_id)) {
                return rest_ensure_response(['status' => 'error']);
            }

            /*
         * Store callback data
         */
            // store relevant data in post meta

            update_post_meta($post_id, 'checkout_id', $checkoutId);
            update_post_meta($post_id, 'status', $status);
            update_post_meta($post_id, 'amount', $this->amount);
            update_post_meta($post_id, 'result_code', $resultCode);
            update_post_meta($post_id, 'result_desc', $resultDesc);
            update_post_meta($post_id, 'account_ref', $this->account_reference ?? '');
            update_post_meta($post_id, 'date', current_time('mysql'));

            return rest_ensure_response(['status' => 'ok']);
        }

        /*
     * ======================================================
     * 2. JS POLLING (GET)
     * ======================================================
     */
        $checkoutId = sanitize_text_field($request->get_param('checkout_id'));
        $phone = sanitize_text_field($request->get_param('phone'));


        if (!$checkoutId || !$phone) {
            return rest_ensure_response([
                'status'  => 'error',
                'message' => 'No checkout id or phone provided',
            ]);
        }

        $posts = get_posts([
            'post_type'   => 'mpesa',
            'meta_key'    => 'checkout_id',
            'meta_value'  => $checkoutId,
            'numberposts' => 1,
        ]);

        if (!$posts) {
            return rest_ensure_response([
                'status'  => 'pending',
                'message' => 'Waiting for payment confirmation',
            ]);
        }

        $post_id = $posts[0]->ID;

        //store phone number in post meta
        update_post_meta($post_id, 'phone_number', $phone);

        $status      = get_post_meta($post_id, 'status', true);
        $result_desc = get_post_meta($post_id, 'result_desc', true);

        if ($status === 'failed') {
            return rest_ensure_response([
                'status'  => 'failed',
                'message' => $result_desc ?: 'Payment was cancelled or failed',
            ]);
        }

        if ($status === 'success') {
            return rest_ensure_response([
                'status'  => 'success',
                'message' => $result_desc ?: 'Payment successful',
            ]);
        }

        // fallback (should rarely happen)
        return rest_ensure_response([
            'status'  => 'pending',
            'message' => 'Waiting for payment confirmation',
        ]);
    }
}
