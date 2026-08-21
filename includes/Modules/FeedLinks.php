<?php
/**
 * Removes RSS/Atom feed link tags from wp_head.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\Registry;

/**
 * Removes `<link rel="alternate">` feed tags. The feed URLs themselves stay.
 *
 * @since 0.1.0
 */
final class FeedLinks extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'feed_links';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Feed-Links im Kopf entfernen', 'bs-overhead-toggles' );
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
		return __( 'Entfernt die RSS-Hinweise, die WordPress in jede Seite schreibt.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Die Feed-Verweise im HTML-Kopf (Beiträge, Kommentare, Kategorien) werden nicht mehr ausgegeben. Die Feeds unter der üblichen Adresse bleiben erreichbar.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Aufgeräumterer Quellcode, ohne etwas an der eigentlichen Feed-Funktion zu ändern.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Feed-Reader, die die Adresse nur aus dem HTML-Kopf lesen, finden sie nicht mehr von allein. Wer die Adresse kennt oder ein Lesezeichen hat, ist nicht betroffen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'feed_links (wp_head, 2); feed_links_extra (wp_head, 3)';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
	}
}
