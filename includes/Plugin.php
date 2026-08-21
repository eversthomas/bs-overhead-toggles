<?php
/**
 * Plugin bootstrap and composition root.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles;

defined( 'ABSPATH' ) || exit;

/**
 * Single entry point after autoloading is in place.
 *
 * Phase 0: textdomain, activation bookkeeping, hook points for later phases.
 * Settings, module registry and admin UI are intentionally not wired yet.
 *
 * @since 0.1.0
 */
final class Plugin {

	/**
	 * Option key for the stored plugin version.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public const VERSION_OPTION = 'bsot_version';

	/**
	 * Singleton instance.
	 *
	 * @since 0.1.0
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Returns the shared plugin instance.
	 *
	 * @since 0.1.0
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Prevents external construction.
	 *
	 * @since 0.1.0
	 */
	private function __construct() {}

	/**
	 * Prevents cloning.
	 *
	 * @since 0.1.0
	 */
	private function __clone() {}

	/**
	 * Prevents unserialization.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function __wakeup(): void {
		throw new \LogicException( 'Cannot unserialize ' . self::class );
	}

	/**
	 * Runs on plugin activation.
	 *
	 * Does not write default toggle settings yet — that belongs to Phase 1
	 * (Settings API). Stores the plugin version for later migrations.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function activate(): void {
		if ( false === get_option( self::VERSION_OPTION, false ) ) {
			add_option( self::VERSION_OPTION, BSOT_VERSION, '', false );
			return;
		}

		update_option( self::VERSION_OPTION, BSOT_VERSION, false );
	}

	/**
	 * Runs on plugin deactivation.
	 *
	 * Options are left in place; deletion belongs in uninstall.php (later).
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		// Intentionally empty in Phase 0.
	}

	/**
	 * Wires plugin hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function boot(): void {
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Loads translations from /languages.
	 *
	 * WordPress 6.7+ also just-in-time loads from the plugin header; this
	 * call remains explicit so the domain path is guaranteed.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'bs-overhead-toggles',
			false,
			dirname( BSOT_BASENAME ) . '/languages'
		);
	}
}
