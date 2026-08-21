<?php
/**
 * Public theme/plugin query API for toggle state.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles;

defined( 'ABSPATH' ) || exit;

/**
 * Read-only accessors used by `bsot_is_disabled()` and `bsot_get_option()`.
 *
 * Safe to call from `after_setup_theme` or `init`. Options are loaded on
 * `plugins_loaded`; module hooks attach on `init` priority 5.
 *
 * @since 0.1.0
 */
final class Api {

	/**
	 * Whether a feature is effectively disabled (toggle on, not locked).
	 *
	 * @since 0.1.0
	 *
	 * @param string      $feature Module id, e.g. `gutenberg`.
	 * @param string|null $context Optional sub-context (post type, `frontend`, …).
	 * @return bool
	 */
	public static function is_disabled( string $feature, ?string $context = null ): bool {
		$toggle = Plugin::instance()->registry()->get( $feature );

		if ( ! $toggle ) {
			return false;
		}

		return $toggle->is_disabled_in_context( $context );
	}

	/**
	 * Raw stored value for a module or a dotted nested key.
	 *
	 * Examples: `gutenberg`, `gutenberg.post_types`, `heartbeat.frontend`.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key     Module id or `id.subkey`.
	 * @param mixed  $default Fallback when missing.
	 * @return mixed
	 */
	public static function get_option( string $key, mixed $default = null ): mixed {
		$parts = explode( '.', $key, 2 );
		$id    = $parts[0];
		$toggle = Plugin::instance()->registry()->get( $id );

		if ( ! $toggle ) {
			return $default;
		}

		$options = Settings::get_all();
		$value   = array_key_exists( $id, $options ) ? $options[ $id ] : $toggle->get_default();

		if ( ! isset( $parts[1] ) ) {
			return $value;
		}

		if ( ! is_array( $value ) || ! array_key_exists( $parts[1], $value ) ) {
			return $default;
		}

		return $value[ $parts[1] ];
	}
}
