<?php
/**
 * Slows or stops the Heartbeat API per context.
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
 *     heartbeat => [
 *         enabled  => bool,
 *         frontend => 'off'|'15'|'30'|'60'|'120',
 *         backend  => 'off'|'15'|'30'|'60'|'120',
 *         editor   => '15'|'30'|'60'|'120', // never off
 *     ]
 *
 * @since 0.1.0
 */
final class Heartbeat extends AbstractToggle {

	/**
	 * Allowed interval tokens including off (frontend/backend only).
	 *
	 * @since 0.1.0
	 *
	 * @var string[]
	 */
	private const INTERVALS = array( 'off', '15', '30', '60', '120' );

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'heartbeat';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Heartbeat drosseln', 'bs-overhead-toggles' );
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
		return __( 'Seltener oder gar nicht im Hintergrund nachfragen — getrennt für Website, Backend und Editor.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'WordPress fragt im Hintergrund regelmäßig nach, ob jemand noch da ist. Unten stellst du das für die öffentliche Website, das Backend und den Editor getrennt ein. Im Editor lässt sich Heartbeat nicht abschalten, nur strecken — sonst fehlen Autosave und die Sperre, wenn zwei Leute denselben Beitrag öffnen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Weniger dauernde Anfragen an den Server, vor allem auf der Website und in Listen im Backend.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Ohne Heartbeat im Backend fehlen Sitzungs-Hinweise (noch angemeldet?). Im Editor bleiben Autosave und Beitragssperre nur, weil Heartbeat dort nicht ausgeschaltet werden kann. Plugins, die Heartbeat für Live-Updates nutzen, reagieren langsamer oder gar nicht.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'heartbeat_settings interval; wp_dequeue_script(heartbeat) when off; editor (post.php, post-new.php, site-editor.php, customize.php) never deregistered; option bsot_options[heartbeat][enabled|frontend|backend|editor]';
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
			'enabled'  => false,
			'frontend' => 'off',
			'backend'  => '60',
			'editor'   => '60',
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

		$clean['enabled']  = parent::sanitize( $value['enabled'] ?? false );
		$clean['frontend'] = $this->sanitize_interval( $value['frontend'] ?? $clean['frontend'], true );
		$clean['backend']  = $this->sanitize_interval( $value['backend'] ?? $clean['backend'], true );
		$clean['editor']   = $this->sanitize_interval( $value['editor'] ?? $clean['editor'], false );

		return $clean;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_switch_input( bool $enabled, bool $locked ): void {
		printf(
			'<input type="hidden" name="%1$s" value="%2$s" %3$s data-bsot-switch-value />',
			esc_attr( Settings::OPTION_KEY . '[heartbeat][enabled]' ),
			$enabled ? '1' : '0',
			$locked ? 'disabled="disabled"' : ''
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_extra_fields( bool $locked ): void {
		$stored = $this->stored();
		?>
		<fieldset class="bsot-extra" data-bsot-extra <?php echo $this->is_enabled() ? '' : 'hidden'; ?>>
			<legend class="bsot-extra-legend"><?php esc_html_e( 'Wie oft nachfragen', 'bs-overhead-toggles' ); ?></legend>
			<p class="bsot-extra-hint"><?php esc_html_e( '„Aus“ gibt es nur auf der Website und im Backend. Im Editor bleibt ein Intervall, damit Speichern und Sperren funktionieren.', 'bs-overhead-toggles' ); ?></p>
			<?php
			$this->render_select( 'frontend', __( 'Öffentliche Website', 'bs-overhead-toggles' ), (string) $stored['frontend'], true, $locked );
			$this->render_select( 'backend', __( 'Backend (Listen, Dashboard)', 'bs-overhead-toggles' ), (string) $stored['backend'], true, $locked );
			$this->render_select( 'editor', __( 'Editor', 'bs-overhead-toggles' ), (string) $stored['editor'], false, $locked );
			?>
		</fieldset>
		<?php
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		add_filter( 'heartbeat_settings', array( $this, 'filter_settings' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_stop' ), 100 );
		add_action( 'admin_enqueue_scripts', array( $this, 'maybe_stop' ), 100 );
	}

	/**
	 * Sets the Heartbeat interval for the current context.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $settings Heartbeat settings.
	 * @return array<string, mixed>
	 */
	public function filter_settings( array $settings ): array {
		$token = $this->token_for_context();
		if ( 'off' === $token ) {
			return $settings;
		}

		$settings['interval'] = (int) $token;

		return $settings;
	}

	/**
	 * Dequeues Heartbeat when the current context is set to off.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function maybe_stop(): void {
		if ( 'off' !== $this->token_for_context() ) {
			return;
		}

		wp_deregister_script( 'heartbeat' );
		wp_dequeue_script( 'heartbeat' );
	}

	/**
	 * Stored nested options with defaults.
	 *
	 * @since 0.1.0
	 *
	 * @return array{enabled: mixed, frontend: string, backend: string, editor: string}
	 */
	private function stored(): array {
		$options = Settings::get_all();
		$stored  = isset( $options['heartbeat'] ) && is_array( $options['heartbeat'] ) ? $options['heartbeat'] : $this->get_default();
		$default = $this->get_default();

		return array(
			'enabled'  => $stored['enabled'] ?? false,
			'frontend' => (string) ( $stored['frontend'] ?? $default['frontend'] ),
			'backend'  => (string) ( $stored['backend'] ?? $default['backend'] ),
			'editor'   => (string) ( $stored['editor'] ?? $default['editor'] ),
		);
	}

	/**
	 * Interval token for frontend, backend or editor.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	private function token_for_context(): string {
		$stored = $this->stored();

		if ( ! is_admin() ) {
			return $stored['frontend'];
		}

		if ( $this->is_editor_screen() ) {
			return 'off' === $stored['editor'] ? '120' : $stored['editor'];
		}

		return $stored['backend'];
	}

	/**
	 * Whether the current admin screen needs editor Heartbeat.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	private function is_editor_screen(): bool {
		global $pagenow;

		return in_array(
			(string) $pagenow,
			array( 'post.php', 'post-new.php', 'site-editor.php', 'customize.php' ),
			true
		);
	}

	/**
	 * Renders one interval select.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key      frontend|backend|editor.
	 * @param string $label    Row label.
	 * @param string $current  Stored token.
	 * @param bool   $allow_off Whether “off” is offered.
	 * @param bool   $locked    Whether the control is locked.
	 * @return void
	 */
	private function render_select( string $key, string $label, string $current, bool $allow_off, bool $locked ): void {
		$choices = $this->interval_labels( $allow_off );
		?>
		<label class="bsot-select-row">
			<span><?php echo esc_html( $label ); ?></span>
			<select
				class="bsot-select"
				name="<?php echo esc_attr( Settings::OPTION_KEY . '[heartbeat][' . $key . ']' ); ?>"
				<?php disabled( $locked ); ?>
			>
				<?php foreach ( $choices as $value => $text ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>>
						<?php echo esc_html( $text ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</label>
		<?php
	}

	/**
	 * Select labels.
	 *
	 * @since 0.1.0
	 *
	 * @param bool $allow_off Whether to include “off”.
	 * @return array<string, string>
	 */
	private function interval_labels( bool $allow_off ): array {
		$labels = array(
			'15'  => __( 'Alle 15 Sekunden', 'bs-overhead-toggles' ),
			'30'  => __( 'Alle 30 Sekunden', 'bs-overhead-toggles' ),
			'60'  => __( 'Jede Minute', 'bs-overhead-toggles' ),
			'120' => __( 'Alle 2 Minuten', 'bs-overhead-toggles' ),
		);

		if ( $allow_off ) {
			return array( 'off' => __( 'Aus', 'bs-overhead-toggles' ) ) + $labels;
		}

		return $labels;
	}

	/**
	 * Sanitizes one interval token.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $raw       Submitted value.
	 * @param bool  $allow_off Whether “off” is valid.
	 * @return string
	 */
	private function sanitize_interval( mixed $raw, bool $allow_off ): string {
		$token = sanitize_key( (string) $raw );

		if ( ! in_array( $token, self::INTERVALS, true ) ) {
			return $allow_off ? '60' : '60';
		}

		if ( 'off' === $token && ! $allow_off ) {
			return '120';
		}

		return $token;
	}
}
