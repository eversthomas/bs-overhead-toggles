<?php
/**
 * Removes the WordPress version generator tag.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\Registry;

/**
 * @since 0.1.0
 */
final class Generator extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'generator';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'WordPress-Versionsnummer aus dem Quellcode entfernen', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_category(): string {
		return Registry::CAT_PRIVACY;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description(): string {
		return __( 'Entfernt die kleine Versionsangabe, die WordPress standardmäßig im Quellcode hinterlässt.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Nimmt den Generator-Hinweis aus dem HTML-Kopf. Die Website läuft unverändert — nur die Versionszahl steht nicht mehr offen da.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Weniger öffentlicher Fingerabdruck und ein aufgeräumterer Kopf.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Praktisch nie. Nur Diagnose-Tools, die die Version aus dem HTML lesen, sehen sie nicht mehr.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'wp_generator on wp_head';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		remove_action( 'wp_head', 'wp_generator' );
	}
}
