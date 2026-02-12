<?php

/**
 * The main plugin file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://surgetech.co.ke/mpesapaywallpro
 * @since             1.0.0
 * @package           MpesaPaywallPro
 *
 * @wordpress-plugin
 * Plugin Name:       MpesaPaywallPro
 * Plugin URI:        https://surgetech.co.ke/mpesapaywallpro
 * Description:       MpesaPaywallPro is a WordPress plugin that integrates M-Pesa payment gateway and restricts your premium content behind a paywall allowing you to monetize your website effectively.
 * Version:           1.0.0
 * Author:            SurgeTech
 * Author URI:        https://surgetech.co.ke/
 * License:           GNU General Public License v2
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mpesapaywallpro
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
	die;
}

// Autoload dependencies using Composer
$autoload = __DIR__ . '/vendor/autoload.php';
if (! file_exists($autoload)) {
	wp_die(
		'MpesaPaywallPro requires Composer dependencies. Please run <code>composer install</code> in the plugin directory.',
		'MpesaPaywallPro — Missing Dependencies',
		array('exit_status' => 1)
	);
}
require_once $autoload;


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 * 
 */

// setting up plugin constants
define('MPP_VERSION', '1.0.0');
define('MPP_URL', plugin_dir_url(__FILE__));
define('MPP_PATH', plugin_dir_path(__FILE__));
define('MPP_BASENAME', plugin_basename(__FILE__));
define('MPESA_MIN', 1);
define('MPESA_MAX', 150000);
define('MPP_LOG_DIR', dirname(WP_CONTENT_DIR) . '/mpp-logs/');
define('MPP_LOG_EXPIRY_DAYS', 30);
define('MPP_LICENSE_SERVER', 'https://bp-mpesa-gateway-license.vercel.app/api/mpesapaywallpro');

// namespace imports
use MpesaPaywallPro\base\MpesaPaywallProActivator;
use MpesaPaywallPro\base\MpesaPaywallProDeactivator;
use MpesaPaywallPro\base\MpesaPaywallPro;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_mpesapaywallpro()
{
	MpesaPaywallProActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_mpesapaywallpro()
{
	MpesaPaywallProDeactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_mpesapaywallpro');
register_deactivation_hook(__FILE__, 'deactivate_mpesapaywallpro');


/**
 * Initialize plugin update checker if the PucFactory class is available.
 *
 * This code checks if the YahnisElsts PluginUpdateChecker library is loaded
 * and available. If it is, it creates an update checker instance that will
 * periodically check the license server for plugin updates.
 *
 * @since 1.0.0
 */

if (class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
	/**
	 * Build and configure the update checker.
	 *
	 * Parameters:
	 * - url: The remote GitHub repository URL
	 * - __FILE__: The main plugin file path
	 * - 'mpesapaywallpro': Unique slug identifier for this plugin
	 */
	$myUpdateChecker = PucFactory::buildUpdateChecker(
		'https://github.com/peanutsx50/MpesaPaywallPro',
		__FILE__,
		'mpesapaywallpro'
	);

	// Enable releases - this tells the update checker to use GitHub releases
	/** @disregard P1013 Undefined method */
	$myUpdateChecker->getVcsApi()->enableReleaseAssets();
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

function run_mpesapaywallpro()
{

	$plugin = new MpesaPaywallPro();
	$plugin->run();
}
run_mpesapaywallpro();
