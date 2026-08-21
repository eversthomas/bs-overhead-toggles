<?php
/**
 * Removes the RSD (Really Simple Discovery) link from wp_head.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\Registry;

/**
 * WLW-Manifest is gone from core since WP 6.3 — not offered as a toggle.
 *
 * @since 0.1.0
 */
final class Rsd extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'rsd';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'RSD-Link entfernen', 'bs-overhead-toggles' );
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
		return __( 'Entfernt den Hinweis im Kopf, mit dem Schreibprogramme die Website zum Fernbearbeiten finden.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Nimmt den RSD-Link aus dem HTML-Kopf. XML-RPC selbst bleibt an — das ist ein eigener Schalter.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Eine öffentlich sichtbare Schnittstelle weniger im Quellcode.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Desktop-Blogging-Apps, die sich über diesen Link anmelden, finden die Schnittstelle nicht mehr von allein.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'rsd_link on wp_head';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		remove_action( 'wp_head', 'rsd_link' );
	}
}
