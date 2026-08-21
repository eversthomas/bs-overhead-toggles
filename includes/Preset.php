<?php
/**
 * Built-in option presets (quick-setup / reset).
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles;

defined( 'ABSPATH' ) || exit;

/**
 * 90 % standard: frontend/head overhead and XML-RPC off; editor, SEO,
 * REST locks and storage left untouched.
 *
 * @since 0.1.0
 */
final class Preset {

	/**
	 * Module ids turned on by „Standard-Konfiguration anwenden“.
	 *
	 * Nested modules keep their defaults (body_classes → IDs only).
	 *
	 * @since 0.1.0
	 *
	 * @var string[]
	 */
	public const STANDARD_IDS = array(
		'emojis',
		'block_library_css',
		'dashicons',
		'oembed',
		'feed_links',
		'generator',
		'rsd',
		'shortlink',
		'rest_discovery',
		'xmlrpc',
		'self_pingbacks',
		'body_classes',
	);

	/**
	 * Builds a full options array for a named preset.
	 *
	 * Locked modules keep their stored value. Every value is sanitized
	 * through the module so nested defaults (strip groups, intervals) stay valid.
	 *
	 * @since 0.1.0
	 *
	 * @param Registry $registry Module registry.
	 * @param string   $preset   `standard` or `reset`.
	 * @return array<string, mixed>
	 */
	public static function build( Registry $registry, string $preset ): array {
		$clean  = Settings::defaults();
		$stored = Settings::get_all();
		$on     = 'standard' === $preset ? self::STANDARD_IDS : array();

		foreach ( $registry->all() as $toggle ) {
			$id = $toggle->get_id();

			if ( $toggle->is_locked() ) {
				$clean[ $id ] = array_key_exists( $id, $stored ) ? $stored[ $id ] : $toggle->get_default();
				continue;
			}

			$raw          = in_array( $id, $on, true ) ? self::enabled_payload( $toggle ) : $toggle->get_default();
			$clean[ $id ] = $toggle->sanitize( $raw );
		}

		return $clean;
	}

	/**
	 * Turns a module on while keeping nested defaults (e.g. body_classes.strip).
	 *
	 * @since 0.1.0
	 *
	 * @param Toggle $toggle Module.
	 * @return mixed
	 */
	private static function enabled_payload( Toggle $toggle ): mixed {
		$default = $toggle->get_default();

		if ( is_array( $default ) && array_key_exists( 'enabled', $default ) ) {
			$default['enabled'] = true;

			return $default;
		}

		return true;
	}
}
