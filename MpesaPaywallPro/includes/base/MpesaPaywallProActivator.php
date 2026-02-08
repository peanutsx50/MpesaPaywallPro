<?php

/**
 * Fired during plugin activation
 *
 * @link       http://surgetech.co.ke
 * @since      1.0.0
 *
 * @package    mpesapaywallpro
 * @subpackage mpesapaywallpro/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    mpesapaywallpro
 * @subpackage mpesapaywallpro/includes
 * @author     SurgeTech <admin@surgetech.co.ke>
 */

namespace MpesaPaywallPro\base;

use MpesaPaywallPro\core\MpesaPaywallProLogger;

class MpesaPaywallProActivator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		// Initialize the logger
		MpesaPaywallProLogger::info('MpesaPaywallPro plugin activated successfully.');
		self::schedule_log_cleanup();
	}

	/**
	 * Schedule automatic cleanup of old logs (call this during plugin activation)
	 * 
	 * @return void
	 */
	public static function schedule_log_cleanup()
	{
		if (!wp_next_scheduled('mpp_cleanup_old_logs')) {
			wp_schedule_event(time(), 'daily', 'mpp_cleanup_old_logs');
		}
	}
}
