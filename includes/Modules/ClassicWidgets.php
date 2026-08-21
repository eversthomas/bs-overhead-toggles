<?php
/**
 * Restores the classic widgets screen.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\Registry;

/**
 * Independent of the Gutenberg post-editor toggle.
 *
 * @since 0.1.0
 */
final class ClassicWidgets extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'classic_widgets';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Klassische Widgets verwenden', 'bs-overhead-toggles' );
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
	public function get_description(): string {
		return __( 'Die Widget-Seite wieder als Liste statt als Block-Editor anzeigen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Schaltet den Block-Editor nur für Widgets aus. Beiträge und der Website-Editor bleiben davon unberührt.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Die gewohnte Widget-Liste, unabhängig davon, ob Gutenberg für Beiträge an oder aus ist.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Bereits angelegte Block-Widgets können in den Seitenleisten fehlen oder als Kurzcode erscheinen, bis ihr sie als klassische Widgets neu setzt.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'use_widgets_block_editor';
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
		add_filter( 'use_widgets_block_editor', '__return_false' );
	}
}
