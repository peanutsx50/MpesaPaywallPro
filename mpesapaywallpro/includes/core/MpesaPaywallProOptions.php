<?php

/**
 * Singleton class to manage the plugin options.
 * 
 * This class provides methods to get and set the plugin options, as well as to initialize default options on plugin activation.
 * It ensures that there is only one instance of the options manager throughout the plugin, and provides a centralized way to access and modify the plugin settings.
 * The options are stored in the WordPress database using the `get_option` and `update_option` functions, and are defined as an associative array with default values.
 * 
 * 
 * @package MpesaPaywallPro
 * 
 * @since 1.0.0
 * @author Surgetech
 * 
 */

namespace MpesaPaywallPro\Core;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


class MpesaPaywallProOptions {
    private static $options = null;

    /**
     * Get the plugin options.
     * 
     * @return array The plugin options.
     */
    public static function get_options($key = null) {
        if (self::$options === null) {
            self::$options = get_option('mpesapaywallpro_options', []);
        }

        if ($key !== null && isset(self::$options[$key])) {
            return self::$options[$key];
        }
        return self::$options;
    }

    /**
     * Refresh the plugin options.
     * 
     * @param array $options The new plugin options.
     * @return void
     */
    public static function refresh() {
        self::$options = null;
    }
}
