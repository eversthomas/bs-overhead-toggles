<?php
/**
 * Limits or disables post revisions via wp_revisions_to_keep.
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
 *     revisions => [
 *         enabled => bool,
 *         keep    => int, // 0 = none
 *     ]
 *
 * WP_POST_REVISIONS is not locked: this filter still wins.
 *
 * @since 0.1.0
 */
final class Revisions extends AbstractToggle {

	/**
	 * Allowed keep-counts (0 = disable).
	 *
	 * @since 0.1.0
	 *
	 * @var int[]
	 */
	private const KEEP = array( 0, 3, 5, 10 );

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'revisions';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Revisionen begrenzen', 'bs-overhead-toggles' );
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
	public function get_group(): string {
		return Registry::GROUP_STORAGE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description(): string {
		return __( 'Nicht jede Speicherung als eigene alte Fassung behalten — oder gar keine.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'WordPress legt standardmäßig unbegrenzt viele Zwischenstände an. Unten wählst du, wie viele bleiben, oder ob gar keine neuen mehr entstehen. Schon gespeicherte Revisionen in der Datenbank bleiben liegen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Deutlich kleinere Datenbank bei häufig bearbeiteten Beiträgen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Alte Fassungen zum Zurückspringen fehlen oder es gibt nur noch wenige. Bei „Keine Revisionen“ gibt es keinen Verlauf mehr — nur noch den aktuellen Stand und Autosave, falls Heartbeat im Editor läuft.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'wp_revisions_to_keep (not WP_POST_REVISIONS); option bsot_options[revisions][enabled|keep]';
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
			'enabled' => false,
			'keep'    => 5,
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

		$clean['enabled'] = parent::sanitize( $value['enabled'] ?? false );
		$keep             = (int) ( $value['keep'] ?? $clean['keep'] );
		$clean['keep']    = in_array( $keep, self::KEEP, true ) ? $keep : 5;

		return $clean;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_switch_input( bool $enabled, bool $locked ): void {
		printf(
			'<input type="hidden" name="%1$s" value="%2$s" %3$s data-bsot-switch-value />',
			esc_attr( Settings::OPTION_KEY . '[revisions][enabled]' ),
			$enabled ? '1' : '0',
			$locked ? 'disabled="disabled"' : ''
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_extra_fields( bool $locked ): void {
		$options = Settings::get_all();
		$stored  = isset( $options['revisions'] ) && is_array( $options['revisions'] ) ? $options['revisions'] : $this->get_default();
		$current = (int) ( $stored['keep'] ?? 5 );
		$labels  = array(
			0  => __( 'Keine Revisionen', 'bs-overhead-toggles' ),
			3  => __( '3 pro Beitrag', 'bs-overhead-toggles' ),
			5  => __( '5 pro Beitrag', 'bs-overhead-toggles' ),
			10 => __( '10 pro Beitrag', 'bs-overhead-toggles' ),
		);
		?>
		<fieldset class="bsot-extra" data-bsot-extra <?php echo $this->is_enabled() ? '' : 'hidden'; ?>>
			<legend class="bsot-extra-legend"><?php esc_html_e( 'Wie viele alte Fassungen behalten', 'bs-overhead-toggles' ); ?></legend>
			<label class="bsot-select-row">
				<span><?php esc_html_e( 'Anzahl', 'bs-overhead-toggles' ); ?></span>
				<select
					class="bsot-select"
					name="<?php echo esc_attr( Settings::OPTION_KEY . '[revisions][keep]' ); ?>"
					<?php disabled( $locked ); ?>
				>
					<?php foreach ( $labels as $keep => $text ) : ?>
						<option value="<?php echo esc_attr( (string) $keep ); ?>" <?php selected( $current, $keep ); ?>>
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
	public function register(): void {
		add_filter( 'wp_revisions_to_keep', array( $this, 'limit' ), 10, 2 );
	}

	/**
	 * Caps revisions for every post type that supports them.
	 *
	 * @since 0.1.0
	 *
	 * @param int      $num  Current limit.
	 * @param \WP_Post $post Post being saved.
	 * @return int
	 */
	public function limit( int $num, \WP_Post $post ): int {
		unset( $num, $post );

		return $this->keep();
	}

	/**
	 * Stored keep-count.
	 *
	 * @since 0.1.0
	 *
	 * @return int
	 */
	private function keep(): int {
		$options = Settings::get_all();
		$stored  = isset( $options['revisions'] ) && is_array( $options['revisions'] ) ? $options['revisions'] : $this->get_default();
		$value   = (int) ( $stored['keep'] ?? 5 );

		return in_array( $value, self::KEEP, true ) ? $value : 5;
	}
}
