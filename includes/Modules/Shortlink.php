<?php
/**
 * Removes shortlink output from head and HTTP headers.
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
final class Shortlink extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'shortlink';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Kurzlink entfernen', 'bs-overhead-toggles' );
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
		return __( 'Entfernt den Kurzlink, den WordPress zusätzlich zur normalen Adresse ausgibt.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Nimmt den Kurzlink aus dem HTML-Kopf und den zugehörigen HTTP-Header. Die normale Beitragsadresse bleibt.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Aufgeräumterer Kopf und ein Header weniger — die Kurzform brauchen die meisten Websites nicht.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Praktisch nie. Permalinks bleiben. Nur Werkzeuge, die den Kurzlink aus dem Kopf oder Header lesen, finden ihn nicht mehr.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'wp_shortlink_wp_head (wp_head); wp_shortlink_header (template_redirect:11)';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
		remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
	}
}
