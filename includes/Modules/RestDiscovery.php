<?php
/**
 * Removes REST API discovery links from head and HTTP headers.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\Registry;

/**
 * Cosmetic only — does not restrict the API.
 *
 * @since 0.1.0
 */
final class RestDiscovery extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'rest_discovery';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'REST-Hinweis im Kopf entfernen', 'bs-overhead-toggles' );
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
		return __( 'Entfernt den Link zur REST-API aus dem HTML-Kopf. Das ist Kosmetik, kein Sicherheitsgewinn.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Nimmt den REST-Link aus dem HTML-Kopf und den zugehörigen HTTP-Header. Die Schnittstelle selbst bleibt erreichbar.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Aufgeräumterer Quellcode. Wer die API nutzen will, kennt die Adresse trotzdem — dieser Schalter sperrt nichts.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Praktisch nie. Nur Werkzeuge, die die API-Adresse ausschließlich aus diesem Link lesen, finden sie nicht von allein.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'rest_output_link_wp_head (wp_head); rest_output_link_header (template_redirect:11)';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
		remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	}
}
