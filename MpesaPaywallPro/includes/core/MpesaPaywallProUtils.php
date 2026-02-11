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

    /**
     * Get the client's IP address.
     *
     * This function attempts to retrieve the client's IP address from remote addr,
     * It returns a sanitized IP address or 'UNKNOWN' if it cannot be determined.
     *
     * @return string The client's IP address or 'UNKNOWN' if not available.
     * @since 1.0.0
     * @access public
     * @static
     */
    public static function get_client_ip()
    {
        // return remote address with fallback to unknown if not available
        return sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN');
    }

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
}
