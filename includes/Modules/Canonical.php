<?php
/**
 * Removes rel=canonical from wp_head.
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
final class Canonical extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'canonical';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Canonical-Link von WordPress entfernen', 'bs-overhead-toggles' );
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
		return __( 'Entfernt die von WordPress gesetzte bevorzugte Adresse einer Seite.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Nimmt den Canonical-Hinweis aus dem HTML-Kopf. Die Seite selbst ändert sich nicht — nur die Angabe, welche Adresse die „richtige" ist.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Platz für eine eigene SEO-Lösung, ohne dass WordPress und das SEO-Plugin denselben Hinweis doppelt setzen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Ohne Ersatz kann eine Suchmaschine mehrere Adressen derselben Seite als Duplikate werten. Nur abschalten, wenn etwas anderes den Canonical setzt.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'rel_canonical on wp_head';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		remove_action( 'wp_head', 'rel_canonical' );
	}
}
