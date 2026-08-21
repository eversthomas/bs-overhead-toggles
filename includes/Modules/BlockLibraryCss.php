<?php
/**
 * Removes core block-library CSS on the frontend only.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\Registry;

/**
 * Independent of the Gutenberg editor toggle. Editor assets stay loaded.
 *
 * @since 0.1.0
 */
final class BlockLibraryCss extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'block_library_css';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Standard-Block-CSS entfernen', 'bs-overhead-toggles' );
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
		return __( 'Lädt die mitgelieferten Gutenberg-Block-Styles nicht mehr auf der öffentlichen Website.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Entfernt die WordPress-Block-Bibliothek-Styles im Frontend (Sammeldatei und einzelne Block-Dateien). Der Editor im Backend bleibt unverändert.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Deutlich weniger CSS auf jeder Seite — sinnvoll, wenn das Theme Blöcke selbst gestaltet oder ihr keine Core-Block-Optik braucht.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Gutenberg-Blöcke können ungestylt aussehen (Spalten, Buttons, Galerien, Abstand). Nur einschalten, wenn das Theme das selbst übernimmt.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'wp_common_block_scripts_and_styles; wp-block-library / wp-block-library-theme / classic-theme-styles; should_load_separate_core_block_assets (Frontend)';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		remove_action( 'wp_enqueue_scripts', 'wp_common_block_scripts_and_styles' );
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles' );

		add_filter( 'should_load_separate_core_block_assets', '__return_false', 99 );
		add_filter( 'should_load_block_assets_on_demand', '__return_false', 99 );

		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue' ), 100 );
	}

	/**
	 * Dequeues leftover block-library handles on the frontend.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function dequeue(): void {
		if ( is_admin() ) {
			return;
		}

		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'classic-theme-styles' );

		$styles = wp_styles();
		$queued = $styles->queue;

		foreach ( $queued as $handle ) {
			if ( str_starts_with( $handle, 'wp-block-' ) ) {
				wp_dequeue_style( $handle );
			}
		}
	}
}
