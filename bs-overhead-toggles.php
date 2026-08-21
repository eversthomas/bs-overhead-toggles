<?php
/**
 * Plugin Name:       BS Overhead Toggles
 * Plugin URI:        https://bezugssysteme.de
 * Description:       Zentrale Verwaltung abschaltbarer WordPress-Standardfunktionen.
 * Version:           0.1.0
 * Requires at least: 7.0
 * Requires PHP:      8.1
 * Author:            Tom Evers
 * Author URI:        https://bezugssysteme.de
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bs-overhead-toggles
 * Domain Path:       /languages
 *
 * @package BS\OverheadToggles
 */

defined( 'ABSPATH' ) || exit;

define( 'BSOT_VERSION', '0.1.0' );
define( 'BSOT_FILE', __FILE__ );
define( 'BSOT_DIR', plugin_dir_path( __FILE__ ) );
define( 'BSOT_URL', plugin_dir_url( __FILE__ ) );
define( 'BSOT_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Minimum PHP version required by this plugin.
 *
 * @since 0.1.0
 */
define( 'BSOT_MIN_PHP', '8.1' );

/**
 * Minimum WordPress version required by this plugin.
 *
 * @since 0.1.0
 */
define( 'BSOT_MIN_WP', '7.0' );

/**
 * Whether the current environment meets plugin requirements.
 *
 * Kept procedural and syntax-compatible with older PHP so a fatal error
 * is avoided if the plugin files are loaded on an unsupported runtime.
 *
 * @since 0.1.0
 *
 * @return bool
 */
function bsot_requirements_met() {
	if ( version_compare( PHP_VERSION, BSOT_MIN_PHP, '<' ) ) {
		return false;
	}

	global $wp_version;

	if ( ! isset( $wp_version ) || version_compare( $wp_version, BSOT_MIN_WP, '<' ) ) {
		return false;
	}

	return true;
}

/**
 * Admin notice when PHP or WordPress is too old.
 *
 * @since 0.1.0
 *
 * @return void
 */
function bsot_requirements_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$message = sprintf(
		/* translators: 1: plugin name, 2: minimum PHP version, 3: minimum WordPress version */
		__( '%1$s benötigt PHP %2$s oder neuer und WordPress %3$s oder neuer.', 'bs-overhead-toggles' ),
		'<strong>BS Overhead Toggles</strong>',
		esc_html( BSOT_MIN_PHP ),
		esc_html( BSOT_MIN_WP )
	);

	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		wp_kses( $message, array( 'strong' => array() ) )
	);
}

if ( ! bsot_requirements_met() ) {
	add_action( 'admin_notices', 'bsot_requirements_notice' );
	return;
}

require_once BSOT_DIR . 'includes/Autoloader.php';

\BS\OverheadToggles\Autoloader::register();

register_activation_hook( BSOT_FILE, array( \BS\OverheadToggles\Plugin::class, 'activate' ) );
register_deactivation_hook( BSOT_FILE, array( \BS\OverheadToggles\Plugin::class, 'deactivate' ) );

/**
 * Boots the plugin after all plugins have loaded.
 *
 * Runs on `plugins_loaded` so option APIs are available, and still before
 * `wp_functionality_constants()` — later modules can define AUTOSAVE_INTERVAL
 * and EMPTY_TRASH_DAYS here if they are not already set in wp-config.php.
 *
 * @since 0.1.0
 *
 * @return void
 */
function bsot_boot() {
	\BS\OverheadToggles\Plugin::instance()->boot();
}

add_action( 'plugins_loaded', 'bsot_boot' );
