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


    // Mpesa related functions can be added here in the future
    public function __construct()
    {
        // Initialize Mpesa properties from settings
        $this->consumer_key            = get_option('mpesapaywallpro_options')['consumer_key'] ?? '';
        $this->consumer_secret         = get_option('mpesapaywallpro_options')['consumer_secret'] ?? '';
        $this->shortcode               = get_option('mpesapaywallpro_options')['shortcode'] ?? '';
        $this->passkey                 = get_option('mpesapaywallpro_options')['passkey'] ?? '';
        $this->environment             = get_option('mpesapaywallpro_options')['env'] ?? 'sandbox';
        //$this->amount                  = get_option('mpesapaywallpro_options')['default_amount'] ?? 20; // Match form field name
        $this->account_reference       = get_option('mpesapaywallpro_options')['account_reference'] ?? '';
        $this->transaction_description = get_option('mpesapaywallpro_options')['transaction_description'] ?? '';

        $this->access_token = $this->generate_access_token();
        $this->timestamp    = date('YmdHis');
        $this->password     = $this->generate_password();
        $this->callbackurl  = home_url('/wp-json/mpesapaywallpro/v1/callback', 'https');
        $this->url          = $this->environment === 'production' ?
            'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest' :
            'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    }

    // Mpesa STK push request function
    public function send_stk_push_request($phone_number, $amount)
    {
        // check if consumer_key, consumer_secret, shortcode, passkey is empty
        $validation_result = $this->validate_config();
        if ($validation_result['status'] === 'error') {
            return $validation_result;
        }

        // set global amount variable for use in callback
        $this->amount = $amount;

        try {
            $data = [
                "BusinessShortCode" => $this->shortcode, // paybill number
                "Password" => $this->password, // generated password
                "Timestamp" => $this->timestamp, // current timestamp
                "TransactionType" => $this->transactionType, // transaction type (CustomerBuyGoodsOnline or CustomerPayBillOnline)
                "Amount" => $amount, // get amount from settings, do not allow zero or negative amounts
                "PartyA" => $phone_number, // phone number making payment
                "PartyB" => $this->shortcode, // paybill number
                "PhoneNumber" => $phone_number, // similar to pary A
                "AccountReference" => $this->account_reference, // transaction id
                "TransactionDesc" => $this->transaction_description, // description of transaction
                "CallBackURL" => $this->callbackurl, // webhook callback
            ];
            // send request to mpesa api
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->access_token,
                'Content-Type: application/json',
            ]);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            $response = curl_exec($curl);
            $decoded_response = json_decode($response, true);

            // Check for M-Pesa API errors
            if (isset($decoded_response['errorCode'])) {
                return [
                    'status' => 'error',
                    'message' => $decoded_response['errorMessage'] ?? 'M-Pesa API Error',
                    'error_code' => $decoded_response['errorCode'],
                    'response' => $decoded_response
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Payment request sent. Enter your M-Pesa PIN.',
                'response' => $decoded_response,
            ];
        } catch (\Exception $e) {
            $this->err = $e->getMessage();
            return [
                'status' => 'error',
                'message' => 'Exception: ' . $this->err
            ];
        }

        // return ['status' => 'success'];
    }

    // generate access token for mpesa api
    private function generate_access_token()
    {
        $auth_url = $this->environment === 'production' ?
            'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' :
            'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $credentials = base64_encode($this->consumer_key . ':' . $this->consumer_secret);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $auth_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $credentials
        ]);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);

        $result = json_decode($response, true);

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
        error_log('BPMG Callback hit - Method: ' . $request->get_method());

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
                error_log('BPMG: Failed to create mpesa post');
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

        return rest_ensure_response([
            'status'    => get_post_meta($post_id, 'status', true),
            'message'   => get_post_meta($post_id, 'result_desc', true),
            'date'      => get_post_meta($post_id, 'date', true),
        ]);
    }
}
