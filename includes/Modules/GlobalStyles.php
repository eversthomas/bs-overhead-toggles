<?php
/**
 * Removes theme.json global styles on the frontend.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\Registry;

/**
 * Handle `global-styles` is printed as `global-styles-inline-css`.
 * Editor assets are left untouched.
 *
 * @since 0.1.0
 */
final class GlobalStyles extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'global_styles';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'theme.json-Designvariablen entfernen', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_category(): string {
		return Registry::CAT_PERFORMANCE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description(): string {
		return __( 'Entfernt den großen Inline-CSS-Block mit Farben, Schriftgrößen und Abständen aus theme.json.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Lädt die von WordPress erzeugten Design-Variablen (CSS Custom Properties) nicht mehr auf der Website. Der Block-Editor im Backend bleibt unverändert.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Oft der größte CSS-Block im Kopf — weniger Daten auf jeder Seite, wenn das Theme diese Variablen nicht braucht.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Sichtbare Design-Änderungen sind wahrscheinlich: Buttons, Farben, Schriftgrößen und Abstände, die auf diesen Variablen basieren, verlieren ihr Styling. Kein reines Aufräumen — vorher im Frontend prüfen, besonders bei Block-Themes und Classic Themes mit theme.json.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'wp_enqueue_global_styles (wp_enqueue_scripts, wp_footer:1); handles global-styles, wp-global-styles-placeholder';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
		remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );

		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue' ), 100 );
		add_action( 'wp_footer', array( $this, 'dequeue' ), 2 );
	}

	/**
	 * Dequeues leftover global-styles handles on the frontend.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function dequeue(): void {
		if ( is_admin() ) {
			return;
		}

		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'wp-global-styles-placeholder' );
	}
}
