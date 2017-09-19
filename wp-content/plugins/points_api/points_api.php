<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://author.com
 * @since             1.0.0
 * @package           Points_api
 *
 * @wordpress-plugin
 * Plugin Name:       Points API
 * Plugin URI:        http://site.com
 * Description:       Points API provides user-meta control via REST API.
 * Version:           1.0.0
 * Author:            Points API
 * Author URI:        http://author.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       points_api
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PLUGIN_VERSION', '1.0.0' );

$points_api = FALSE;

require plugin_dir_path( __FILE__ ) . 'custom/class-user-hooks.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-points_api-activator.php
 */
function activate_points_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-points_api-activator.php';
	Points_api_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-points_api-deactivator.php
 */
function deactivate_points_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-points_api-deactivator.php';
	Points_api_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_points_api' );
register_deactivation_hook( __FILE__, 'deactivate_points_api' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-points_api.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_points_api() {
	global $points_api;

	$points_api = new Points_api();

	if ( $points_api->get_settings_option( 'points_api_enabled' ) ) {
		$points_api->get_loader()->add_action( 'register_form', UserHooks::getClass(), 'callback_register_form' );
		$points_api->get_loader()->add_action( 'user_register', UserHooks::getClass(), 'callback_user_register' );
		$points_api->get_loader()->add_filter( 'registration_errors', UserHooks::getClass(), 'callback_registration_errors', 10, 3 );
		$points_api->get_loader()->add_filter( 'wp_authenticate_user', UserHooks::getClass(), 'callback_authenticate_user', 10, 2 );
	}

	$points_api->run();
}
run_points_api();
