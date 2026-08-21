<?php
/**
 * Shared defaults for toggle modules.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles;

defined( 'ABSPATH' ) || exit;

/**
 * Base implementation so a new module only fills in copy and hook logic.
 *
 * Constant locks: override `locking_constant()` in the module (e.g. return
 * `AUTOSAVE_INTERVAL`). `ConstantLock` decides whether wp-config.php already
 * defined it; the Settings UI then disables the switch and shows the reason.
 *
 * @since 0.1.0
 */
abstract class AbstractToggle implements Toggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_group(): string {
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_experimental(): bool {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_default(): mixed {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_enabled(): bool {
		$options = Settings::get_all();
		$id      = $this->get_id();

		if ( ! array_key_exists( $id, $options ) ) {
			$default = $this->get_default();

			if ( is_array( $default ) ) {
				return ! empty( $default['enabled'] );
			}

			return (bool) $default;
		}

		$value = $options[ $id ];

		if ( is_array( $value ) ) {
			return ! empty( $value['enabled'] );
		}

		return (bool) $value;
	}

	/**
	 * WordPress constant that, if predefined, makes this toggle ineffective.
	 *
	 * Return an empty string when the module is not bound to a PHP constant.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	protected function locking_constant(): string {
		return '';
	}

	/**
	 * Whether the current request is public HTML (theme, AJAX fragments).
	 *
	 * Skips wp-admin screens and REST (block editor / Site Editor previews).
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	protected function is_frontend_output(): bool {
		if ( function_exists( 'wp_is_serving_rest_request' ) && wp_is_serving_rest_request() ) {
			return false;
		}

		if ( is_admin() && ! wp_doing_ajax() ) {
			return false;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_locked(): bool {
		$constant = $this->locking_constant();

		return '' !== $constant && ConstantLock::is_locked( $constant );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_lock_reason(): string {
		if ( ! $this->is_locked() ) {
			return '';
		}

		return sprintf(
			/* translators: %s: PHP constant name, e.g. AUTOSAVE_INTERVAL */
			__( 'Diese Einstellung steht bereits in der wp-config.php (%s). Der Schalter hier hätte keine Wirkung und bleibt deshalb inaktiv.', 'bs-overhead-toggles' ),
			$this->locking_constant()
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( mixed $value ): mixed {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_int( $value ) || is_float( $value ) ) {
			return 1 === (int) $value;
		}

		if ( is_string( $value ) ) {
			return in_array( strtolower( $value ), array( '1', 'true', 'on', 'yes' ), true );
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function show_warning_inline(): bool {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_switch_input( bool $enabled, bool $locked ): void {
		printf(
			'<input type="hidden" name="%1$s" value="%2$s" %3$s data-bsot-switch-value />',
			esc_attr( Settings::OPTION_KEY . '[' . $this->get_id() . ']' ),
			$enabled ? '1' : '0',
			$locked ? 'disabled="disabled"' : ''
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_extra_fields( bool $locked ): void {
		unset( $locked );
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot_early(): void {}
}
