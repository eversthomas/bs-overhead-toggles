<?php
/**
 * Strips Gutenberg block wrapper classes from frontend HTML.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\HtmlClasses;
use BS\OverheadToggles\Registry;

/**
 * Experimental: class names change between WP versions, and core/theme
 * CSS plus third-party scripts target them. Layout/color classes stay.
 *
 * @since 0.1.0
 */
final class BlockClasses extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'block_classes';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Gutenberg-Block-Klassen aufräumen', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_category(): string {
		return Registry::CAT_CLEANUP;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_group(): string {
		return Registry::GROUP_FRONTEND_CLASSES;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_experimental(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description(): string {
		return __( 'Nimmt wp-block-* und Textausrichtung von Blöcken. Fragil bei Core-Updates.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Entfernt die von Gutenberg erzeugten Block-Klassen (wp-block-…) und die Textausrichtung-Klassen (has-text-align-…). Layout-, Farb- und Schrift-Klassen bleiben. Im Editor ändert sich nichts.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Deutlich weniger Klassen-Rauschen in Block-Inhalten, wenn das Theme nicht auf diese Namen angewiesen ist.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'WordPress- und Theme-CSS hängen an genau diesen Klassen: Blöcke können ungestylt oder ohne Ausrichtung erscheinen. Andere Plugins und Skripte nutzen sie als Haken. Neue WordPress-Versionen können weitere Klassennamen einführen, die dieser Schalter noch nicht kennt. Nur einschalten, wenn das Frontend danach geprüft wird.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'block_default_classname; render_block priority 20 via WP_HTML_Tag_Processor (wp-block, wp-block-{name}, has-text-align-*; keeps wp-block-*__* BEM elements); editor/REST skipped';
	}

	/**
	 * {@inheritdoc}
	 */
	public function show_warning_inline(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		add_filter( 'block_default_classname', array( $this, 'filter_default_classname' ), 99 );
		add_filter( 'render_block', array( $this, 'filter_render_block' ), 20 );
	}

	/**
	 * Suppresses the generated wp-block-{name} on dynamic wrappers.
	 *
	 * @since 0.1.0
	 *
	 * @param string $classname Generated class name.
	 * @return string
	 */
	public function filter_default_classname( string $classname ): string {
		if ( ! $this->is_frontend_output() ) {
			return $classname;
		}

		return '';
	}

	/**
	 * Strips matching classes from rendered block HTML.
	 *
	 * @since 0.1.0
	 *
	 * @param string $content Block HTML.
	 * @return string
	 */
	public function filter_render_block( string $content ): string {
		if ( '' === $content || ! $this->is_frontend_output() ) {
			return $content;
		}

		return HtmlClasses::strip(
			$content,
			fn( string $class ): bool => $this->should_strip( $class )
		);
	}

	/**
	 * Whether a class is a Gutenberg wrapper or text-align class.
	 *
	 * @since 0.1.0
	 *
	 * @param string $class Class name.
	 * @return bool
	 */
	private function should_strip( string $class ): bool {
		if ( 'wp-block' === $class || str_starts_with( $class, 'has-text-align-' ) ) {
			return true;
		}

		// Wrapper class only — keep BEM elements like wp-block-button__link.
		return (bool) preg_match( '/^wp-block-[a-z0-9-]+$/', $class );
	}
}
