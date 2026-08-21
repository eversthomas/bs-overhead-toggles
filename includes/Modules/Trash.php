<?php
/**
 * Sets trash retention via EMPTY_TRASH_DAYS.
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
 *     trash => [
 *         enabled => bool,
 *         days    => int, // 0 = delete immediately
 *     ]
 *
 * Defined on plugins_loaded, before wp_functionality_constants().
 *
 * @since 0.1.0
 */
final class Trash extends AbstractToggle {

	/**
	 * Allowed retention days.
	 *
	 * @since 0.1.0
	 *
	 * @var int[]
	 */
	private const DAYS = array( 0, 7, 14, 30 );

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'trash';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Papierkorb kürzer behalten', 'bs-overhead-toggles' );
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
		return __( 'Gelöschte Inhalte nicht 30 Tage im Papierkorb lassen — oder gleich endgültig löschen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'WordPress hält gelöschte Beiträge und Seiten standardmäßig 30 Tage im Papierkorb. Unten wählst du eine kürzere Frist. „Sofort endgültig“ überspringt den Papierkorb — das lässt sich nicht rückgängig machen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Weniger tote Datensätze in der Datenbank, schneller leerer Papierkorb.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Kürzere Frist heißt: versehentlich Gelöschtes ist früher weg. Bei „Sofort endgültig“ gibt es keinen Papierkorb — gelöscht ist gelöscht.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'define EMPTY_TRASH_DAYS on plugins_loaded (before wp_functionality_constants); 0 disables trash; option bsot_options[trash][enabled|days]';
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
	protected function locking_constant(): string {
		return 'EMPTY_TRASH_DAYS';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_default(): mixed {
		return array(
			'enabled' => false,
			'days'    => 7,
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
		$days             = (int) ( $value['days'] ?? $clean['days'] );
		$clean['days']    = in_array( $days, self::DAYS, true ) ? $days : 7;

		return $clean;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_switch_input( bool $enabled, bool $locked ): void {
		printf(
			'<input type="hidden" name="%1$s" value="%2$s" %3$s data-bsot-switch-value />',
			esc_attr( Settings::OPTION_KEY . '[trash][enabled]' ),
			$enabled ? '1' : '0',
			$locked ? 'disabled="disabled"' : ''
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_extra_fields( bool $locked ): void {
		$options = Settings::get_all();
		$stored  = isset( $options['trash'] ) && is_array( $options['trash'] ) ? $options['trash'] : $this->get_default();
		$current = (int) ( $stored['days'] ?? 7 );
		$labels  = array(
			7  => __( '7 Tage', 'bs-overhead-toggles' ),
			14 => __( '14 Tage', 'bs-overhead-toggles' ),
			30 => __( '30 Tage (WordPress-Standard)', 'bs-overhead-toggles' ),
			0  => __( 'Sofort endgültig löschen', 'bs-overhead-toggles' ),
		);
		?>
		<fieldset class="bsot-extra" data-bsot-extra <?php echo $this->is_enabled() ? '' : 'hidden'; ?>>
			<legend class="bsot-extra-legend"><?php esc_html_e( 'Wie lange im Papierkorb', 'bs-overhead-toggles' ); ?></legend>
			<label class="bsot-select-row">
				<span><?php esc_html_e( 'Frist', 'bs-overhead-toggles' ); ?></span>
				<select
					class="bsot-select"
					name="<?php echo esc_attr( Settings::OPTION_KEY . '[trash][days]' ); ?>"
					<?php disabled( $locked ); ?>
				>
					<?php foreach ( $labels as $days => $text ) : ?>
						<option value="<?php echo esc_attr( (string) $days ); ?>" <?php selected( $current, $days ); ?>>
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
		if ( defined( 'EMPTY_TRASH_DAYS' ) ) {
			return;
		}

		define( 'EMPTY_TRASH_DAYS', $this->days() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		// Constant is defined in boot_early() before wp_functionality_constants().
	}

	/**
	 * Stored retention in days.
	 *
	 * @since 0.1.0
	 *
	 * @return int
	 */
	private function days(): int {
		$options = Settings::get_all();
		$stored  = isset( $options['trash'] ) && is_array( $options['trash'] ) ? $options['trash'] : $this->get_default();
		$value   = (int) ( $stored['days'] ?? 7 );

		return in_array( $value, self::DAYS, true ) ? $value : 7;
	}
}
