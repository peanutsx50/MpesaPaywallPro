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
	public static function activate() {
		// Initialize the logger
		MpesaPaywallProLogger::info('MpesaPaywallPro plugin activated successfully.');
	}
}
