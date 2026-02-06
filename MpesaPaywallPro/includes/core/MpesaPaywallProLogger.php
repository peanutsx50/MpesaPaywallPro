<?php

/**
 * MpesaPaywallPro logger core class.
 * 
 * This class provides logging functionality for the MpesaPaywallPro plugin, 
 * allowing developers to log important events and errors during plugin execution. 
 * It supports different log levels (info, warning, error) and can be easily extended 
 * to include additional log levels or output formats.
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

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MpesaPaywallProLogger
{

    private static $log_file = null;
    private static $writable = null; // null = not checked, true = writable, false = not writable
    private static $fallback_enabled = false;
    private static $max_log_size = 10485760; // 10MB default
    private static $init_errors = [];
    private static $date = null;

    public static function init()
    {
        self::$date = date('Y-m-d');;
        self::$log_file = MPP_LOG_DIR . 'activity-' . self::$date . '.log';
        self::$init_errors = [];

        // Validate MPP_PATH is defined
        if (!defined('MPP_LOG_DIR') || empty(MPP_LOG_DIR)) {
            self::$init_errors[] = 'MPP_LOG_DIR constant is not defined';
            self::$writable = false;
            self::$fallback_enabled = true;
            return false;
        }

        $log_dir = dirname(self::$log_file);

        // Create logs directory if it doesn't exist
        if (!is_dir($log_dir)) {
            if (!wp_mkdir_p($log_dir)) {
                self::$init_errors[] = "Failed to create log directory: {$log_dir}";
                self::$writable = false;
                self::$fallback_enabled = true;
                return false;
            }
        }

        // Check if directory is writable
        if (!is_writable($log_dir)) {
            self::$init_errors[] = "Log directory is not writable: {$log_dir}";
            self::$writable = false;
            self::$fallback_enabled = true;
            return false;
        }

        // Create log file if it doesn't exist
        if (!file_exists(self::$log_file)) {
            if (@file_put_contents(self::$log_file, '') === false) {
                self::$init_errors[] = "Failed to create log file: " . self::$log_file;
                self::$writable = false;
                self::$fallback_enabled = true;
                return false;
            }
        }

        // Verify file is writable
        if (!is_writable(self::$log_file)) {
            self::$init_errors[] = "Log file is not writable: " . self::$log_file;
            self::$writable = false;
            self::$fallback_enabled = true;
            return false;
        }

        self::$writable = true;
        return true;
    }

    public static function log($level, $message, $context = [])
    {
        // Lazy initialization if not already done
        if (self::$writable === null) {
            self::init();
        }

        // Validate inputs
        if (empty($message)) {
            return false;
        }

        // Rotate log if it exceeds max size
        self::rotate_log_if_large();

        // Sanitize inputs
        $level = strtoupper(sanitize_text_field($level));
        $message = is_string($message) ? $message : print_r($message, true);

        $timestamp = current_time('Y-m-d H:i:s');
        $ip = self::get_client_ip();

        // Safely encode context
        $context_json = 'null';
        if (!empty($context)) {
            $encoded = @json_encode($context, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
            $context_json = ($encoded !== false) ? $encoded : json_encode(['error' => 'Failed to encode context']);
        }

        $log_line = sprintf(
            "[%s] [%s] [%s] %s | Context: %s\n",
            $timestamp,
            $level,
            $ip,
            $message,
            $context_json
        );

        // Attempt to write to custom log file
        if (self::$writable) {

            $result = @error_log($log_line, 3, self::$log_file);

            if ($result === false) {
                self::$writable = false; // Mark as not writable to prevent further attempts
                self::$fallback_enabled = true;
                self::fallback_log($level, $message, $context);
                return false;
            }

            return true;
        }

        // Fallback to WordPress error log or PHP error log
        if (self::$fallback_enabled) {
            self::fallback_log($level, $message, $context);
        }

        return false;
    }

    /**
     * Rotate log file if it exceeds max size
     */
    private static function rotate_log_if_large()
    {
        if (!file_exists(self::$log_file)) {
            return;
        }

        $file_size = @filesize(self::$log_file);

        if ($file_size === false || $file_size < self::$max_log_size) {
            return;
        }

        self::init(); // Re-initialize to create new log file
    }

    /**
     * Fallback logging when custom log file is unavailable
     */
    private static function fallback_log($level, $message, $context = [])
    {
        // Safely encode context with fallback
        $context_str = '';
        if (!empty($context)) {
            $encoded = @json_encode($context, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
            $context_str = ($encoded !== false) ? $encoded : json_encode(['error' => 'Failed to encode context']);
        }

        $timestamp = current_time('Y-m-d H:i:s');
        $fallback_message = sprintf(
            '[MpesaPaywallPro] [%s] LEVEL: [%s] MESSAGE: [%s] CONTEXT: [%s]',
            $timestamp,
            $level,
            $message,
            $context_str
        );

        // Try WordPress debug.log if WP_DEBUG_LOG is enabled
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log($fallback_message);
        } else {
            // Fall back to PHP's default error log
            @error_log($fallback_message);
        }
    }

    public static function error($message, $context = [])
    {
        return self::log('error', $message, $context);
    }

    public static function warning($message, $context = [])
    {
        return self::log('warning', $message, $context);
    }

    public static function info($message, $context = [])
    {
        return self::log('info', $message, $context);
    }

    /**
     * Get initialization errors
     */
    public static function get_init_errors()
    {
        return self::$init_errors;
    }

    /**
     * Check if logger is operational
     */
    public static function get_writable_status()
    {
        if (self::$writable === null) {
            self::init();
        }

        return self::$writable === true;
    }

    /**
     * Get client IP address with fallback handling
     */
    private static function get_client_ip()
    {
        $ip = 'UNKNOWN';

        // Try various methods to get IP
        $ip_keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip_list = explode(',', $_SERVER[$key]);
                $ip = trim($ip_list[0]);

                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    break;
                }
            }
        }

        return $ip;
    }

    /**
     * Manually clear/reset the log file
     */
    public static function clear_log()
    {
        if (self::$log_file && file_exists(self::$log_file)) {
            return @file_put_contents(self::$log_file, '') !== false;
        }

        return false;
    }
}
