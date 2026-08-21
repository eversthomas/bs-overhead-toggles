<?php
/**
 * Locks the REST API for logged-out visitors, with a route prefix whitelist.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\Registry;
use BS\OverheadToggles\Settings;

/**
 * Nested option shape:
 *
 *     rest_guests => [
 *         enabled    => bool,
 *         whitelist  => string[], // route prefixes, e.g. contact-form-7
 *     ]
 *
 * Runs after core cookie/application-password checks so authenticated
 * requests are never blocked. Application Passwords, cookies and nonces
 * are not offered as toggles.
 *
 * @since 0.1.0
 */
final class RestGuests extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'rest_guests';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'REST-API für Gäste sperren', 'bs-overhead-toggles' );
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
		return __( 'Nur angemeldete Nutzer dürfen die REST-API nutzen — mit Ausnahmen für einzelne Pfade.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Gäste bekommen keinen Zugriff auf die REST-API, außer auf die unten eingetragenen Pfade. Angemeldete Nutzer (auch mit Application Passwords) bleiben unberührt. Anmelden, Cookies und Sicherheitsprüfungen sind nicht abschaltbar.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Weniger öffentliche Schnittfläche, ohne die Bearbeitung im Backend zu stören.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Formulare, Shops, Cookie-Banner und eigene Endpunkte, die ohne Login funken, schlagen fehl — es sei denn, ihr tragt ihren Pfad unten als Ausnahme ein. Den Kern-Pfad wp/v2 als Ganzes einzutragen würde die Sperre praktisch aufheben.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'rest_authentication_errors priority 110 (after cookie/app-password); option bsot_options[rest_guests][enabled|whitelist]';
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
	public function get_default(): mixed {
		return array(
			'enabled'   => false,
			'whitelist' => array(),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( mixed $value ): mixed {
		$clean = $this->get_default();

		if ( ! is_array( $value ) ) {
			$clean['enabled'] = parent::sanitize( $value );
			return $clean;
		}

		$clean['enabled']    = parent::sanitize( $value['enabled'] ?? false );
		$clean['whitelist']  = $this->sanitize_prefixes( $value['whitelist'] ?? array() );

		return $clean;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_switch_input( bool $enabled, bool $locked ): void {
		printf(
			'<input type="hidden" name="%1$s" value="%2$s" %3$s data-bsot-switch-value />',
			esc_attr( Settings::OPTION_KEY . '[rest_guests][enabled]' ),
			$enabled ? '1' : '0',
			$locked ? 'disabled="disabled"' : ''
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_extra_fields( bool $locked ): void {
		$options   = Settings::get_all();
		$stored    = isset( $options['rest_guests'] ) && is_array( $options['rest_guests'] ) ? $options['rest_guests'] : $this->get_default();
		$whitelist = isset( $stored['whitelist'] ) && is_array( $stored['whitelist'] ) ? $stored['whitelist'] : array();
		?>
		<fieldset class="bsot-extra" data-bsot-extra <?php echo $this->is_enabled() ? '' : 'hidden'; ?>>
			<legend class="bsot-extra-legend"><?php esc_html_e( 'Ausnahmen (ein Pfad pro Zeile)', 'bs-overhead-toggles' ); ?></legend>
			<p class="bsot-extra-hint"><?php esc_html_e( 'Nur der Anfang des API-Pfads, zum Beispiel contact-form-7 oder bs-fabric. Nicht wp/v2 als Ganzes eintragen.', 'bs-overhead-toggles' ); ?></p>
			<textarea
				class="bsot-textarea"
				name="<?php echo esc_attr( Settings::OPTION_KEY . '[rest_guests][whitelist]' ); ?>"
				rows="4"
				<?php disabled( $locked ); ?>
				placeholder="contact-form-7&#10;bs-fabric"
			><?php echo esc_textarea( implode( "\n", $whitelist ) ); ?></textarea>
		</fieldset>
		<?php
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		add_filter( 'rest_authentication_errors', array( $this, 'restrict_guests' ), 110 );
	}

	/**
	 * Blocks unauthenticated REST requests unless the route is whitelisted.
	 *
	 * @since 0.1.0
	 *
	 * @param \WP_Error|null|true $result Auth result so far.
	 * @return \WP_Error|null|true
	 */
	public function restrict_guests( mixed $result ): mixed {
		if ( true === $result || is_wp_error( $result ) ) {
			return $result;
		}

		if ( is_user_logged_in() ) {
			return $result;
		}

		if ( $this->route_is_allowed() ) {
			return $result;
		}

		return new \WP_Error(
			'rest_not_logged_in',
			__( 'Du musst angemeldet sein, um die REST-API zu nutzen.', 'bs-overhead-toggles' ),
			array( 'status' => 401 )
		);
	}

	/**
	 * Whether the current REST route matches a stored prefix.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	private function route_is_allowed(): bool {
		$options    = Settings::get_all();
		$stored     = isset( $options['rest_guests'] ) && is_array( $options['rest_guests'] ) ? $options['rest_guests'] : $this->get_default();
		$whitelist  = isset( $stored['whitelist'] ) && is_array( $stored['whitelist'] ) ? $stored['whitelist'] : array();
		$route      = $this->current_route();

		if ( '' === $route || array() === $whitelist ) {
			return false;
		}

		foreach ( $whitelist as $prefix ) {
			$normalized = '/' . strtolower( trim( (string) $prefix, '/' ) );
			if ( '/' === $normalized ) {
				continue;
			}
			if ( str_starts_with( strtolower( $route ), $normalized ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Current REST route from the main query, with a leading slash.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	private function current_route(): string {
		if ( empty( $GLOBALS['wp']->query_vars['rest_route'] ) || ! is_string( $GLOBALS['wp']->query_vars['rest_route'] ) ) {
			return '';
		}

		return '/' . ltrim( $GLOBALS['wp']->query_vars['rest_route'], '/' );
	}

	/**
	 * Sanitizes whitelist prefixes from a textarea or array.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $raw Submitted whitelist.
	 * @return string[]
	 */
	private function sanitize_prefixes( mixed $raw ): array {
		if ( is_string( $raw ) ) {
			$raw = preg_split( '/\R/', $raw ) ?: array();
		}

		if ( ! is_array( $raw ) ) {
			return array();
		}

		$clean = array();

		foreach ( $raw as $line ) {
			$line = strtolower( trim( (string) $line, " \t\n\r\0\x0B/" ) );
			if ( '' === $line || strlen( $line ) > 100 ) {
				continue;
			}
			if ( ! preg_match( '/^[a-z0-9\/_-]+$/', $line ) ) {
				continue;
			}
			$clean[] = $line;
			if ( count( $clean ) >= 50 ) {
				break;
			}
		}

		return array_values( array_unique( $clean ) );
	}
}
