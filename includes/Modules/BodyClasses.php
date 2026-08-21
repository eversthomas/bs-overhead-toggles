<?php
/**
 * Strips configurable WordPress body classes from the frontend.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\HtmlClasses;
use BS\OverheadToggles\Registry;
use BS\OverheadToggles\Settings;

/**
 * Nested option shape:
 *
 *     body_classes => [
 *         enabled => bool,
 *         strip   => string[], // group keys from self::groups()
 *     ]
 *
 * @since 0.1.0
 */
final class BodyClasses extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'body_classes';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Body-Klassen aufräumen', 'bs-overhead-toggles' );
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
		return Registry::GROUP_FRONTEND_CLASSES;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description(): string {
		return __( 'Nimmt überflüssige Standardklassen vom body-Element — unten wählst du, welche.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Filtert die automatisch vergebenen Klassen am body-Element. Eigene Klassen aus dem Theme bleiben. Unten schaltest du einzelne Gruppen an oder aus.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Kürzeres HTML ohne IDs und Status-Kennzeichen, die im Alltag selten gebraucht werden.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Theme-CSS, das auf diese Klassen zielt, greift nicht mehr. Besonders „Admin-Leiste“: Ohne die Klasse admin-bar rutscht der Inhalt oft unter die Leiste. „Angemeldet“ blendet per CSS oft Login-Links aus — das fällt dann aus.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'body_class priority 99; option bsot_options[body_classes][enabled|strip]';
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
			'strip'   => array( 'ids' ),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( mixed $value ): mixed {
		$clean = $this->get_default();
		$clean['strip'] = array();

		if ( ! is_array( $value ) ) {
			$clean['enabled'] = parent::sanitize( $value );
			return $clean;
		}

		$clean['enabled'] = parent::sanitize( $value['enabled'] ?? false );

		$submitted = $value['strip'] ?? array();
		if ( ! is_array( $submitted ) ) {
			$submitted = array();
		}

		$allowed        = array_keys( $this->groups() );
		$clean['strip'] = array_values(
			array_intersect(
				$allowed,
				array_map( 'sanitize_key', $submitted )
			)
		);

		return $clean;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_switch_input( bool $enabled, bool $locked ): void {
		printf(
			'<input type="hidden" name="%1$s" value="%2$s" %3$s data-bsot-switch-value />',
			esc_attr( Settings::OPTION_KEY . '[body_classes][enabled]' ),
			$enabled ? '1' : '0',
			$locked ? 'disabled="disabled"' : ''
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_extra_fields( bool $locked ): void {
		$options = Settings::get_all();
		$stored  = isset( $options['body_classes'] ) && is_array( $options['body_classes'] ) ? $options['body_classes'] : $this->get_default();
		$strip   = isset( $stored['strip'] ) && is_array( $stored['strip'] ) ? $stored['strip'] : array();
		?>
		<fieldset class="bsot-extra" data-bsot-extra <?php echo $this->is_enabled() ? '' : 'hidden'; ?>>
			<legend class="bsot-extra-legend"><?php esc_html_e( 'Diese Gruppen entfernen', 'bs-overhead-toggles' ); ?></legend>
			<p class="bsot-extra-hint"><?php esc_html_e( 'Ohne Häkchen ändert der Schalter nichts. Technische Klassennamen stehen klein daneben.', 'bs-overhead-toggles' ); ?></p>
			<?php foreach ( $this->groups() as $key => $group ) : ?>
				<label class="bsot-check">
					<input
						type="checkbox"
						name="<?php echo esc_attr( Settings::OPTION_KEY . '[body_classes][strip][]' ); ?>"
						value="<?php echo esc_attr( $key ); ?>"
						<?php checked( in_array( $key, $strip, true ) ); ?>
						<?php disabled( $locked ); ?>
					/>
					<?php echo esc_html( $group['label'] ); ?>
					<span class="bsot-check-key"><?php echo esc_html( $group['classes'] ); ?></span>
				</label>
			<?php endforeach; ?>
		</fieldset>
		<?php
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		add_filter( 'body_class', array( $this, 'filter_classes' ), 99 );
	}

	/**
	 * Drops selected WordPress body classes.
	 *
	 * @since 0.1.0
	 *
	 * @param string[] $classes Body class names.
	 * @return string[]
	 */
	public function filter_classes( array $classes ): array {
		if ( ! $this->is_frontend_output() ) {
			return $classes;
		}

		$strip = $this->selected_groups();
		if ( array() === $strip ) {
			return $classes;
		}

		return HtmlClasses::filter_list(
			$classes,
			fn( string $class ): bool => $this->matches( $class, $strip )
		);
	}

	/**
	 * Stored strip-group keys.
	 *
	 * @since 0.1.0
	 *
	 * @return string[]
	 */
	private function selected_groups(): array {
		$options = Settings::get_all();
		$stored  = isset( $options['body_classes'] ) && is_array( $options['body_classes'] ) ? $options['body_classes'] : $this->get_default();

		return isset( $stored['strip'] ) && is_array( $stored['strip'] ) ? $stored['strip'] : array();
	}

	/**
	 * Whether a class belongs to any selected strip group.
	 *
	 * @since 0.1.0
	 *
	 * @param string   $class Class name.
	 * @param string[] $strip Selected group keys.
	 * @return bool
	 */
	private function matches( string $class, array $strip ): bool {
		foreach ( $strip as $group ) {
			if ( $this->matches_group( $class, $group ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether a class belongs to one strip group.
	 *
	 * @since 0.1.0
	 *
	 * @param string $class Class name.
	 * @param string $group Group key.
	 * @return bool
	 */
	private function matches_group( string $class, string $group ): bool {
		return match ( $group ) {
			'ids'        => (bool) preg_match( '/^(postid|page-id|attachmentid|parent-pageid|author|category|tag|term)-\d+$/', $class ),
			'logged_in'  => 'logged-in' === $class,
			'admin_bar'  => in_array( $class, array( 'admin-bar', 'no-customize-support' ), true ),
			'templates'  => (bool) preg_match( '/^[a-z0-9_-]+-template(-|$)/', $class ),
			'wp_markers' => in_array( $class, array( 'wp-singular', 'wp-custom-logo', 'wp-embed-responsive' ), true ),
			'paged'      => 'paged' === $class || (bool) preg_match( '/^(paged|[a-z0-9_-]+-paged)-\d+$/', $class ),
			default      => false,
		};
	}

	/**
	 * Selectable strip groups for the extra fields.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, array{label: string, classes: string}>
	 */
	private function groups(): array {
		return array(
			'ids'        => array(
				'label'   => __( 'Beitrags- und Seiten-IDs', 'bs-overhead-toggles' ),
				'classes' => 'postid-*, page-id-*',
			),
			'logged_in'  => array(
				'label'   => __( 'Angemeldet-Kennzeichen', 'bs-overhead-toggles' ),
				'classes' => 'logged-in',
			),
			'admin_bar'  => array(
				'label'   => __( 'Admin-Leiste', 'bs-overhead-toggles' ),
				'classes' => 'admin-bar',
			),
			'templates'  => array(
				'label'   => __( 'Vorlagen-Namen', 'bs-overhead-toggles' ),
				'classes' => 'page-template-*',
			),
			'wp_markers' => array(
				'label'   => __( 'WordPress-Kennzeichen', 'bs-overhead-toggles' ),
				'classes' => 'wp-singular, wp-custom-logo',
			),
			'paged'      => array(
				'label'   => __( 'Seitennummer', 'bs-overhead-toggles' ),
				'classes' => 'paged, paged-*',
			),
		);
	}
}
