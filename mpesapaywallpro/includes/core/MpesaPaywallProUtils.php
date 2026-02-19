<?php

/**
 * MpesaPaywallPro utilities core class.
 * 
 * This class provides utility functions for the MpesaPaywallPro plugin,
 * including functions for sanitization, validation, and other common tasks that
 * are used throughout the plugin. It serves as a helper class to keep the codebase
 * 
 */

namespace MpesaPaywallPro\core;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MpesaPaywallProUtils
{

    public static function safaricom_ips()
    {
        return [
            '196.201.212.0/22',   // Covers most Safaricom IPs
            '196.201.214.0/24',
        ];
    }

    public static function is_safaricom_ip($ip)
    {
        $safaricom_ips = self::safaricom_ips();
        foreach ($safaricom_ips as $range) {
            if (self::ip_in_range($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    private static function ip_in_range($ip, $range)
    {
        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        return ($ip & $mask) === $subnet;
    }

    public static function check_phone_number($number)
    {
        $preg_match = '/^254(7(?:[0129][0-9]|4[0-3568]|5[7-9]|6[89])|11[0-5])\d{6}$/';

        /** @disregard P1010 Undefined type */
        return preg_match($preg_match, $number);
    }

    // Decrypt when reading
    public static function encrypt_credential($value)
    {
        if (empty($value)) return '';
        return base64_encode(openssl_encrypt($value, 'AES-256-CBC', wp_salt('auth'), 0, substr(wp_salt('nonce'), 0, 16)));
    }

    public static function decrypt_credential($value)
    {
        if (empty($value)) return '';
        return openssl_decrypt(base64_decode($value), 'AES-256-CBC', wp_salt('auth'), 0, substr(wp_salt('nonce'), 0, 16));
    }

    // rate limit endpoints
    public static function rate_limit_exceeded($ip, $phone_number, $max_request = 5, $time_window = 2, $ban_threshold = 8)
    {
        $base_key   = 'mpp_rate_limit_' . md5($ip . '_' . $phone_number);
        $count_key  = $base_key . '_count';
        $ban_key    = $base_key . '_ban';

        // If already banned, reject immediately without touching the count
        if (get_transient($ban_key) !== false) {
            return true;
        }

        $request_count = (int) get_transient($count_key);

        // First request in this window
        if ($request_count === 0) {
            set_transient($count_key, 1, $time_window * MINUTE_IN_SECONDS);
            return false;
        }

        $new_count = $request_count + 1;

        // Escalate to a ban — use a separate key so the 1-hour window is stable
        if ($new_count >= $ban_threshold) {
            set_transient($ban_key, 1, HOUR_IN_SECONDS);
            delete_transient($count_key);
            return true;
        }

        // Soft limit — still within the original window
        if ($new_count > $max_request) {
            set_transient($count_key, $new_count, $time_window * MINUTE_IN_SECONDS);
            return true;
        }

        set_transient($count_key, $new_count, $time_window * MINUTE_IN_SECONDS);
        return false;
    }
}
