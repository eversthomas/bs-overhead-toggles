<?php
/**
 * Snapshot of WordPress constants defined before this plugin runs.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles;

defined( 'ABSPATH' ) || exit;

/**
 * Captures AUTOSAVE_INTERVAL and EMPTY_TRASH_DAYS at `plugins_loaded`,
 * before this plugin may define them from saved options.
 *
 * `defined( 'AUTOSAVE_INTERVAL' )` later is useless as a UI check: after we
 * set the constant ourselves it would always look „locked". This snapshot
 * records only pre-existing defines (typically wp-config.php).
 *
 * @since 0.1.0
 */
final class ConstantLock {

	/**
	 * Constants that cannot be overridden from a plugin once defined.
	 *
	 * WP_POST_REVISIONS is omitted: `wp_revisions_to_keep` still works.
	 *
	 * @since 0.1.0
	 *
	 * @var string[]
	 */
	private const WATCHED = array(
		'AUTOSAVE_INTERVAL',
		'EMPTY_TRASH_DAYS',
	);

	/**
	 * Map of constant name => true when predefined.
	 *
	 * @since 0.1.0
	 *
	 * @var array<string, bool>
	 */
	private static array $predefined = array();

	/**
	 * Whether capture() has already run.
	 *
	 * @since 0.1.0
	 *
	 * @var bool
	 */
	private static bool $captured = false;

	/**
	 * Records which watched constants are already defined.
	 *
	 * Must run at the start of Plugin::boot() on `plugins_loaded`, before any
	 * module calls define().
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function capture(): void {
		if ( self::$captured ) {
			return;
		}

		foreach ( self::WATCHED as $constant ) {
			if ( defined( $constant ) ) {
				self::$predefined[ $constant ] = true;
			}
		}

		self::$captured = true;
	}

	/**
	 * Whether a watched constant was already set outside this plugin.
	 *
	 * @since 0.1.0
	 *
	 * @param string $constant Constant name.
	 * @return bool
	 */
	public static function is_locked( string $constant ): bool {
		return ! empty( self::$predefined[ $constant ] );
	}
}
