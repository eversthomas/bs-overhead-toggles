<?php
/**
 * Disables XML-RPC, including unauthenticated pingback methods.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\Registry;

/**
 * `xmlrpc_enabled` alone leaves pingbacks open. Emptying `xmlrpc_methods`
 * is what actually shuts the endpoint down.
 *
 * @since 0.1.0
 */
final class Xmlrpc extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'xmlrpc';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'XML-RPC deaktivieren', 'bs-overhead-toggles' );
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
		return __( 'Schaltet die alte Fernsteuerungs-Schnittstelle komplett aus, inklusive Pingbacks.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'xmlrpc.php antwortet nicht mehr mit Methoden. Auch Pingbacks und Trackbacks über diesen Weg fallen weg. Die REST-API bleibt ein eigener Schalter.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Weniger Angriffsfläche durch eine Schnittstelle, die die allermeisten Sites heute nicht mehr brauchen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Jetpack, ältere mobile Blogging-Apps und Dienste, die noch über XML-RPC schreiben, funktionieren nicht mehr. Andere Blogs können euch keine Pingbacks mehr schicken. Die WordPress-App und die REST-API sind davon unabhängig.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'xmlrpc_enabled; xmlrpc_methods → []; wp_headers removes X-Pingback; bloginfo_url pingback_url emptied';
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
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'xmlrpc_methods', '__return_empty_array' );
		add_filter( 'wp_headers', array( $this, 'strip_pingback_header' ) );
		add_filter( 'bloginfo_url', array( $this, 'strip_pingback_url' ), 10, 2 );
	}

	/**
	 * Drops the X-Pingback HTTP header.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, string> $headers Response headers.
	 * @return array<string, string>
	 */
	public function strip_pingback_header( array $headers ): array {
		unset( $headers['X-Pingback'] );

		return $headers;
	}

	/**
	 * Empties the pingback URL advertised via bloginfo().
	 *
	 * @since 0.1.0
	 *
	 * @param string $output bloginfo value.
	 * @param string $show   Which bloginfo field.
	 * @return string
	 */
	public function strip_pingback_url( string $output, string $show ): string {
		if ( 'pingback_url' === $show ) {
			return '';
		}

		return $output;
	}
}
