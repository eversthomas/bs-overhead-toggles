<?php
/**
 * Stops WordPress from pinging its own URLs when a post is published.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\Registry;

/**
 * Filters `pre_ping` and drops links that point at this site.
 *
 * @since 0.1.0
 */
final class SelfPingbacks extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'self_pingbacks';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Eigen-Pingbacks verhindern', 'bs-overhead-toggles' );
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
		return __( 'Wenn ein Beitrag auf eine eigene Seite verlinkt, entsteht kein Pingback-Kommentar mehr.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Beim Veröffentlichen prüft WordPress Links im Text. Zeigt ein Link auf diese Website, wird er nicht angepingt. Pingbacks von anderen Sites bleiben davon unberührt — dafür ist der XML-RPC-Schalter zuständig.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Keine selbst erzeugten „Dieser Beitrag verlinkt auf …“-Kommentare in der Moderation.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Praktisch nie. Nur wenn ihr Eigen-Pingbacks bewusst als interne Verweis-Liste nutzt, fehlen die Einträge.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'pre_ping; unsets links that start with home_url()';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		add_action( 'pre_ping', array( $this, 'strip_self_links' ) );
	}

	/**
	 * Removes this site's URLs from the ping queue.
	 *
	 * @since 0.1.0
	 *
	 * @param string[] $links URLs about to be pinged (passed by reference).
	 * @return void
	 */
	public function strip_self_links( array &$links ): void {
		$home = home_url();

		foreach ( $links as $index => $link ) {
			if ( str_starts_with( (string) $link, $home ) ) {
				unset( $links[ $index ] );
			}
		}
	}
}
