<?php
/**
 * Strips matching CSS class names from HTML and class lists.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles;

defined( 'ABSPATH' ) || exit;

/**
 * Shared helper for frontend class-cleanup modules.
 *
 * Uses WP_HTML_Tag_Processor so class attributes are rewritten without regex
 * on the whole markup.
 *
 * @since 0.1.0
 */
final class HtmlClasses {

	/**
	 * Removes matching class names from tags in an HTML fragment.
	 *
	 * @since 0.1.0
	 *
	 * @param string                $html         HTML fragment.
	 * @param callable(string):bool $should_strip True when the class name should go.
	 * @param string[]              $tag_names    Lowercase tag names to inspect; empty = all tags.
	 * @return string
	 */
	public static function strip( string $html, callable $should_strip, array $tag_names = array() ): string {
		if ( '' === $html || ! class_exists( \WP_HTML_Tag_Processor::class ) ) {
			return $html;
		}

		$allowed = array();
		foreach ( $tag_names as $tag_name ) {
			$allowed[] = strtoupper( (string) $tag_name );
		}

		$processor = new \WP_HTML_Tag_Processor( $html );

		while ( $processor->next_tag() ) {
			if ( array() !== $allowed && ! in_array( (string) $processor->get_tag(), $allowed, true ) ) {
				continue;
			}

			$class = $processor->get_attribute( 'class' );
			if ( ! is_string( $class ) || '' === $class ) {
				continue;
			}

			$names = array();
			foreach ( $processor->class_list() as $name ) {
				$names[] = $name;
			}

			foreach ( $names as $name ) {
				if ( $should_strip( $name ) ) {
					$processor->remove_class( $name );
				}
			}
		}

		return $processor->get_updated_html();
	}

	/**
	 * Drops matching names from a class list, preserving order.
	 *
	 * @since 0.1.0
	 *
	 * @param string[]                 $classes      Class names.
	 * @param callable(string):bool $should_strip True when the class name should go.
	 * @return string[]
	 */
	public static function filter_list( array $classes, callable $should_strip ): array {
		$kept = array();

		foreach ( $classes as $class ) {
			$class = trim( (string) $class );
			if ( '' === $class || $should_strip( $class ) ) {
				continue;
			}
			$kept[] = $class;
		}

		return array_values( $kept );
	}
}
