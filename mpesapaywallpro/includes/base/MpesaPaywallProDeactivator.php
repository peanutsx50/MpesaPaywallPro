<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://surgetech.co.ke
 * @since      1.0.0
 *
 * @package    mpesapaywallpro
 * @subpackage mpesapaywallpro/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    mpesapaywallpro
 * @subpackage mpesapaywallpro/includes
 * @author     SurgeTech <admin@surgetech.co.ke>
 */

namespace MpesaPaywallPro\base;

use MpesaPaywallPro\core\MpesaPaywallProLogger;

class MpesaPaywallProDeactivator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate()
	{
		MpesaPaywallProLogger::info('MpesaPaywallPro plugin deactivated successfully.');
		self::unschedule_log_cleanup();
		self::stop_token_refresh_schedule();
	}

	/**
	 * Unschedule automatic cleanup (call this during plugin deactivation)
	 * 
	 * @return void
	 */
	public static function unschedule_log_cleanup()
	{
		$timestamp = wp_next_scheduled('mpp_cleanup_old_logs');
		if ($timestamp) {
			wp_unschedule_event($timestamp, 'mpp_cleanup_old_logs');
		}
	}

	public static function stop_token_refresh_schedule()
	{
		$timestamp = wp_next_scheduled('mpp_refresh_access_token');
		if ($timestamp) {
			wp_unschedule_event($timestamp, 'mpp_refresh_access_token');
		}
	}
}
