<?php
/**
 * Plugin Name: Bavaria Events Crawler
 * Plugin URI: https://thevikingcompany.eu
 * Description: Automatically crawls events from Bavaria Yachts website and displays them in a formatted table on your WordPress site with English-to-Romanian translation support.
 * Version: 1.0.0
 * Author: The Viking Company
 * Author URI: https://thevikingcompany.eu
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: bavaria-events
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'BAVARIA_EVENTS_VERSION', '1.0.0' );
define( 'BAVARIA_EVENTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BAVARIA_EVENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BAVARIA_EVENTS_INCLUDES_DIR', BAVARIA_EVENTS_PLUGIN_DIR . 'includes/' );
define( 'BAVARIA_EVENTS_ADMIN_DIR', BAVARIA_EVENTS_PLUGIN_DIR . 'admin/' );
define( 'BAVARIA_EVENTS_PUBLIC_DIR', BAVARIA_EVENTS_PLUGIN_DIR . 'public/' );
define( 'BAVARIA_EVENTS_ASSETS_URL', BAVARIA_EVENTS_PLUGIN_URL . 'assets/' );
define( 'BAVARIA_EVENTS_SOURCE_URL', 'https://www.bavariayachts.com/we-are-bavaria/boat-shows-and-events/' );
define( 'BAVARIA_EVENTS_CRON_HOOK', 'bavaria_events_weekly_crawl' );
define( 'BAVARIA_EVENTS_CRAWL_SCHEDULE', 'weekly' );

// Autoloader
spl_autoload_register( function( $class ) {
	if ( strpos( $class, 'Bavaria_Events' ) === 0 ) {
		$file = BAVARIA_EVENTS_PLUGIN_DIR . 'includes/' . str_replace(
			[ 'Bavaria_Events_', '_' ],
			[ '', '-' ],
			$class
		) . '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
} );

// Load classes
require_once BAVARIA_EVENTS_INCLUDES_DIR . 'class-db-handler.php';
require_once BAVARIA_EVENTS_INCLUDES_DIR . 'class-crawler.php';
require_once BAVARIA_EVENTS_INCLUDES_DIR . 'class-translator.php';
require_once BAVARIA_EVENTS_INCLUDES_DIR . 'class-cron-scheduler.php';
require_once BAVARIA_EVENTS_INCLUDES_DIR . 'class-shortcode-renderer.php';
require_once BAVARIA_EVENTS_INCLUDES_DIR . 'class-avada-integrator.php';
require_once BAVARIA_EVENTS_ADMIN_DIR . 'class-admin-menu.php';
require_once BAVARIA_EVENTS_PUBLIC_DIR . 'class-public-display.php';

/**
 * Initialize plugin
 */
function bavaria_events_init() {
	// Admin initialization
	if ( is_admin() ) {
		Bavaria_Events_Admin_Menu::init();
	}

	// Frontend initialization
	if ( ! is_admin() ) {
		Bavaria_Events_Public_Display::init();
	}

	// Initialize shortcodes
	Bavaria_Events_Shortcode_Renderer::register();

	// Initialize Avada integration
	Bavaria_Events_Avada_Integrator::init();
}

add_action( 'plugins_loaded', 'bavaria_events_init' );

/**
 * Plugin activation
 */
function bavaria_events_activate() {
	// Create database tables
	Bavaria_Events_DB_Handler::create_tables();

	// Schedule weekly cron
	Bavaria_Events_Cron_Scheduler::schedule_cron();

	// Run initial crawl
	do_action( BAVARIA_EVENTS_CRON_HOOK );
}

register_activation_hook( __FILE__, 'bavaria_events_activate' );

/**
 * Plugin deactivation
 */
function bavaria_events_deactivate() {
	// Unschedule cron
	Bavaria_Events_Cron_Scheduler::unschedule_cron();
}

register_deactivation_hook( __FILE__, 'bavaria_events_deactivate' );

/**
 * Plugin uninstall
 */
function bavaria_events_uninstall() {
	// Drop tables if they exist
	Bavaria_Events_DB_Handler::drop_tables();

	// Remove plugin options
	delete_option( 'bavaria_events_google_api_key' );
	delete_option( 'bavaria_events_enable_translation' );
	delete_option( 'bavaria_events_language' );
	delete_option( 'bavaria_events_avada_div_id' );
}

register_uninstall_hook( __FILE__, 'bavaria_events_uninstall' );
