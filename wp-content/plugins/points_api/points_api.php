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
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
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

	$plugin = new Points_api();
	$plugin->run();

}
run_points_api();

require plugin_dir_path( __FILE__ ) . 'custom/class-settings-page.php';
require plugin_dir_path( __FILE__ ) . 'custom/user-hooks.php';


