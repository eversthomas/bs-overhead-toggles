<?php
/**
 * PSR-4 autoloader for the BS\OverheadToggles namespace.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles;

defined( 'ABSPATH' ) || exit;

/**
 * Maps `BS\OverheadToggles\*` to files under `includes/`.
 *
 * Example: `BS\OverheadToggles\Modules\Emojis` → `includes/Modules/Emojis.php`.
 *
 * @since 0.1.0
 */
final class Autoloader {

	/**
	 * Namespace prefix with trailing backslash.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	private const PREFIX = 'BS\\OverheadToggles\\';

	/**
	 * Registers the autoloader with SPL.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function register(): void {
		spl_autoload_register( array( self::class, 'load' ) );
	}

	/**
	 * Loads a class file if it belongs to this plugin's namespace.
	 *
	 * @since 0.1.0
	 *
	 * @param string $class Fully qualified class name.
	 * @return void
	 */
	public static function load( string $class ): void {
		if ( ! str_starts_with( $class, self::PREFIX ) ) {
			return;
		}

		$relative = substr( $class, strlen( self::PREFIX ) );
		$relative = str_replace( '\\', DIRECTORY_SEPARATOR, $relative );
		$file     = BSOT_DIR . 'includes/' . $relative . '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
}
