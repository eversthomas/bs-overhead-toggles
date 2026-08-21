<?php
/**
 * Blocks unauthenticated access to the users REST endpoints.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\Registry;

/**
 * Leaves `/wp/v2/users/me` intact for logged-in editor clients.
 *
 * @since 0.1.0
 */
final class RestUsers extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'rest_users';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Benutzerliste für Gäste sperren', 'bs-overhead-toggles' );
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
		return __( 'Verhindert, dass Unangemeldete Benutzernamen über die REST-API auslesen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Die Benutzer-Endpunkte antworten für Gäste nicht mehr. Angemeldete Nutzer und „wer bin ich“ (/users/me) bleiben erreichbar.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Weniger leichtes Auslesen von Anmeldenamen über eine öffentlich bekannte Adresse.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Öffentliche Autor:innen-Listen über die REST-API (zum Beispiel ein Headless-Frontend) funktionieren ohne Anmeldung nicht mehr.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'rest_pre_dispatch; routes /wp/v2/users and /wp/v2/users/{id}; /users/me is not blocked';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		add_filter( 'rest_pre_dispatch', array( $this, 'block_guest_users' ), 10, 3 );
	}

	/**
	 * Rejects unauthenticated user collection/item requests.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed            $result  Dispatch result so far.
	 * @param \WP_REST_Server  $server  Server instance.
	 * @param \WP_REST_Request $request Current request.
	 * @return mixed
	 */
	public function block_guest_users( mixed $result, \WP_REST_Server $server, \WP_REST_Request $request ): mixed {
		unset( $server );

		if ( is_user_logged_in() ) {
			return $result;
		}

		$route = untrailingslashit( $request->get_route() );

		if ( ! preg_match( '#^/wp/v2/users(?:/[\d]+)?$#', $route ) ) {
			return $result;
		}

		return new \WP_Error(
			'rest_user_cannot_view',
			__( 'Du bist nicht berechtigt, Benutzer anzuzeigen.', 'bs-overhead-toggles' ),
			array( 'status' => 401 )
		);
	}
}
