<?php
/**
 * Removes the img sizes=auto CSS fix and the matching HTML attribute.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\Registry;

/**
 * Same back-compat pattern as emoji styles: unhooking
 * `wp_print_auto_sizes_contain_css_fix` makes the enqueue function no-op.
 *
 * @since 0.1.0
 */
final class ImgAutoSizes extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'img_auto_sizes';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Bild-Auto-Sizes-CSS entfernen', 'bs-overhead-toggles' );
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
		return __( 'Entfernt den eigenen Inline-CSS-Block, den WordPress für automatisch berechnete Bildgrößen ausgibt.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Nimmt das kleine CSS für sizes="auto" aus dem Kopf und setzt die zugehörige Angabe an Bildern nicht mehr. Unabhängig von Gutenberg.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Ein Inline-Block weniger im Kopf auf jeder Seite. Der Block ist klein, hängt aber an einem eigenen Hook und bleibt sonst stehen, wenn nur das Block-CSS aus ist.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Bei manchen Themes kann sich der reservierte Platz für Bilder minimal ändern (leichtes Springen beim Laden). Nur testen, wenn ihr das CSS nicht braucht.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'wp_enqueue_img_auto_sizes_contain_css_fix (wp_head:0); wp_print_auto_sizes_contain_css_fix (wp_head:1); wp_img_tag_add_auto_sizes; handle wp-img-auto-sizes-contain';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		remove_action( 'wp_head', 'wp_enqueue_img_auto_sizes_contain_css_fix', 0 );
		remove_action( 'wp_head', 'wp_print_auto_sizes_contain_css_fix', 1 );

		add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );

		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue' ), 100 );
	}

	/**
	 * Dequeues the auto-sizes handle if it was registered anyway.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function dequeue(): void {
		if ( is_admin() ) {
			return;
		}

		wp_dequeue_style( 'wp-img-auto-sizes-contain' );
	}
}
