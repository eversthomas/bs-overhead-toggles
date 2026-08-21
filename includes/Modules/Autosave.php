<?php
/**
 * Lengthens the autosave interval via AUTOSAVE_INTERVAL.
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
 *     autosave => [
 *         enabled  => bool,
 *         interval => int, // seconds
 *     ]
 *
 * Defined on plugins_loaded, before wp_functionality_constants().
 *
 * @since 0.1.0
 */
final class Autosave extends AbstractToggle {

	/**
	 * Allowed intervals in seconds.
	 *
	 * @since 0.1.0
	 *
	 * @var int[]
	 */
	private const INTERVALS = array( 120, 180, 300 );

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'autosave';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Autosave seltener', 'bs-overhead-toggles' );
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
		return __( 'Zwischenspeichern im Editor nicht jede Minute, sondern seltener.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'WordPress speichert Entwürfe im Editor automatisch. Unten wählst du, wie oft das passiert. Der Standard ist eine Minute.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Weniger Schreibzugriffe während der Bearbeitung, besonders bei langen Sessions.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Geht der Browser dazwischen verloren, ist der letzte Stand älter. Heartbeat im Editor muss an bleiben, sonst greift Autosave gar nicht.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'define AUTOSAVE_INTERVAL on plugins_loaded (before wp_functionality_constants); option bsot_options[autosave][enabled|interval]';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function locking_constant(): string {
		return 'AUTOSAVE_INTERVAL';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_default(): mixed {
		return array(
			'enabled'  => false,
			'interval' => 120,
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
		$interval          = (int) ( $value['interval'] ?? $clean['interval'] );
		$clean['interval'] = in_array( $interval, self::INTERVALS, true ) ? $interval : 120;

		return $clean;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_switch_input( bool $enabled, bool $locked ): void {
		printf(
			'<input type="hidden" name="%1$s" value="%2$s" %3$s data-bsot-switch-value />',
			esc_attr( Settings::OPTION_KEY . '[autosave][enabled]' ),
			$enabled ? '1' : '0',
			$locked ? 'disabled="disabled"' : ''
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_extra_fields( bool $locked ): void {
		$options  = Settings::get_all();
		$stored   = isset( $options['autosave'] ) && is_array( $options['autosave'] ) ? $options['autosave'] : $this->get_default();
		$current  = (int) ( $stored['interval'] ?? 120 );
		$labels   = array(
			120 => __( 'Alle 2 Minuten', 'bs-overhead-toggles' ),
			180 => __( 'Alle 3 Minuten', 'bs-overhead-toggles' ),
			300 => __( 'Alle 5 Minuten', 'bs-overhead-toggles' ),
		);
		?>
		<fieldset class="bsot-extra" data-bsot-extra <?php echo $this->is_enabled() ? '' : 'hidden'; ?>>
			<legend class="bsot-extra-legend"><?php esc_html_e( 'Abstand zwischen Zwischenspeichern', 'bs-overhead-toggles' ); ?></legend>
			<label class="bsot-select-row">
				<span><?php esc_html_e( 'Intervall', 'bs-overhead-toggles' ); ?></span>
				<select
					class="bsot-select"
					name="<?php echo esc_attr( Settings::OPTION_KEY . '[autosave][interval]' ); ?>"
					<?php disabled( $locked ); ?>
				>
					<?php foreach ( $labels as $seconds => $text ) : ?>
						<option value="<?php echo esc_attr( (string) $seconds ); ?>" <?php selected( $current, $seconds ); ?>>
							<?php echo esc_html( $text ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot_early(): void {
		if ( defined( 'AUTOSAVE_INTERVAL' ) ) {
			return;
		}

		define( 'AUTOSAVE_INTERVAL', $this->interval() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		// Constant is defined in boot_early() before wp_functionality_constants().
	}

	/**
	 * Stored interval in seconds.
	 *
	 * @since 0.1.0
	 *
	 * @return int
	 */
	private function interval(): int {
		$options = Settings::get_all();
		$stored  = isset( $options['autosave'] ) && is_array( $options['autosave'] ) ? $options['autosave'] : $this->get_default();
		$value   = (int) ( $stored['interval'] ?? 120 );

		return in_array( $value, self::INTERVALS, true ) ? $value : 120;
	}
}
