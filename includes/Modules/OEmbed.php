<?php
/**
 * Removes oEmbed discovery links and host JS.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\Registry;

/**
 * Discovery is hooked twice (priority 4 and 10); host JS is gated by
 * `has_action( 'wp_head', 'wp_oembed_add_host_js' )`.
 *
 * @since 0.1.0
 */
final class OEmbed extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'oembed';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Einbettungs-Erkennung entfernen', 'bs-overhead-toggles' );
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
		return __( 'Entfernt die oEmbed-Links im Kopf und das Skript für eingebettete WordPress-Beiträge.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Nimmt die Erkennungslinks aus dem HTML-Kopf und verhindert, dass das Einbettungs-Skript geladen wird. Die REST-Schnittstelle für Einbettungen bleibt bestehen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Weniger Angaben im Kopf und kein extra JavaScript, nur weil irgendwo ein WordPress-Beitrag eingebettet sein könnte.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Andere Websites finden eure Beiträge nicht mehr automatisch zum Einbetten. Bereits eingebettete WordPress-Beiträge auf eurer Seite werden nicht mehr von selbst in der Höhe angepasst.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'wp_oembed_add_discovery_links (wp_head 4 + 10); wp_oembed_add_host_js; wp-embed script';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 4 );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );

		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_script' ), 100 );
		add_action( 'wp_footer', array( $this, 'dequeue_script' ), 1 );
	}

	/**
	 * Dequeues the embed host script if it was enqueued later.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function dequeue_script(): void {
		if ( is_admin() ) {
			return;
		}

		wp_dequeue_script( 'wp-embed' );
		wp_deregister_script( 'wp-embed' );
	}
}
