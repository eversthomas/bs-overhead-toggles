<?php
/**
 * Disables the block editor globally, with per-post-type exceptions.
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
 * Nested option shape (strip_default_css lives in BlockLibraryCss, not here):
 *
 *     gutenberg => [
 *         enabled    => bool,     // module on = Classic Editor globally
 *         post_types => string[], // Gutenberg allowed again for these
 *     ]
 *
 * Maps to the plan's `enabled_globally` as the inverse of `enabled`.
 *
 * @since 0.1.0
 */
final class Gutenberg extends AbstractToggle {

	/**
	 * Post types that must keep the block editor (Site Editor / FSE).
	 *
	 * @since 0.1.0
	 *
	 * @var string[]
	 */
	private const SKIP_TYPES = array(
		'attachment',
		'wp_template',
		'wp_template_part',
		'wp_navigation',
		'wp_block',
		'wp_font_family',
		'wp_font_face',
		'wp_global_styles',
	);

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'gutenberg';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Gutenberg-Editor deaktivieren', 'bs-overhead-toggles' );
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
	public function get_description(): string {
		return __( 'Beiträge und Seiten wieder mit dem klassischen Editor bearbeiten, optional mit Ausnahmen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Schaltet den Block-Editor für normale Inhalte aus. Unten kannst du einzelne Inhaltstypen ausnehmen, falls du später eigene Blöcke nur dort brauchst. Der Website-Editor (Design → Editor) bleibt ein Block-Editor.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Leichtere Bearbeitung ohne Block-Oberfläche — der übliche Stand in klassischen Themes. Frontend-Block-CSS ist ein eigener Schalter.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		$text = __( 'Das ist kein kompletter Gutenberg-Kill. Der Website-Editor (Design → Editor) und Vorlagen von Block-Themes nutzen weiter Blöcke. Vorhandene Block-Inhalte bleiben in der Datenbank; im klassischen Editor siehst du sie als Code. Block-Widgets sind ein eigener Schalter.', 'bs-overhead-toggles' );

		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			$text .= ' ' . __( 'Achtung: Das aktive Theme ist ein Block-Theme. Ohne Block-Editor lässt sich das Design dort nicht sinnvoll bearbeiten. Diesen Schalter nur setzen, wenn ihr das bewusst in Kauf nehmt oder das Theme wechselt.', 'bs-overhead-toggles' );
		}

		return $text;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'use_block_editor_for_post_type; option bsot_options[gutenberg][enabled|post_types]; FSE types (wp_template, …) are not filtered';
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
			'enabled'    => false,
			'post_types' => array(),
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

		$submitted = $value['post_types'] ?? array();
		if ( ! is_array( $submitted ) ) {
			$submitted = array();
		}

		$allowed = array_keys( $this->content_post_types() );
		$clean['post_types'] = array_values(
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
			esc_attr( Settings::OPTION_KEY . '[gutenberg][enabled]' ),
			$enabled ? '1' : '0',
			$locked ? 'disabled="disabled"' : ''
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_extra_fields( bool $locked ): void {
		$options    = Settings::get_all();
		$stored     = isset( $options['gutenberg'] ) && is_array( $options['gutenberg'] ) ? $options['gutenberg'] : $this->get_default();
		$selected   = isset( $stored['post_types'] ) && is_array( $stored['post_types'] ) ? $stored['post_types'] : array();
		$post_types = $this->content_post_types();

		if ( array() === $post_types ) {
			return;
		}
		?>
		<fieldset class="bsot-extra" data-bsot-extra <?php echo $this->is_enabled() ? '' : 'hidden'; ?>>
			<legend class="bsot-extra-legend"><?php esc_html_e( 'Gutenberg trotzdem erlauben für', 'bs-overhead-toggles' ); ?></legend>
			<p class="bsot-extra-hint"><?php esc_html_e( 'Nur diese Inhaltstypen behalten den Block-Editor. Alle anderen nutzen den klassischen Editor.', 'bs-overhead-toggles' ); ?></p>
			<?php foreach ( $post_types as $slug => $label ) : ?>
				<label class="bsot-check">
					<input
						type="checkbox"
						name="<?php echo esc_attr( Settings::OPTION_KEY . '[gutenberg][post_types][]' ); ?>"
						value="<?php echo esc_attr( $slug ); ?>"
						<?php checked( in_array( $slug, $selected, true ) ); ?>
						<?php disabled( $locked ); ?>
					/>
					<?php echo esc_html( $label ); ?>
					<span class="bsot-check-key"><?php echo esc_html( $slug ); ?></span>
				</label>
			<?php endforeach; ?>
		</fieldset>
		<?php
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		add_filter( 'use_block_editor_for_post_type', array( $this, 'filter_block_editor' ), 100, 2 );
	}

	/**
	 * Disables the block editor except for allow-listed content types and FSE types.
	 *
	 * @since 0.1.0
	 *
	 * @param bool   $use       Whether to use the block editor.
	 * @param string $post_type Post type slug.
	 * @return bool
	 */
	public function filter_block_editor( bool $use, string $post_type ): bool {
		if ( in_array( $post_type, self::SKIP_TYPES, true ) ) {
			return $use;
		}

		$options    = Settings::get_all();
		$gutenberg  = isset( $options['gutenberg'] ) && is_array( $options['gutenberg'] ) ? $options['gutenberg'] : $this->get_default();
		$exceptions = isset( $gutenberg['post_types'] ) && is_array( $gutenberg['post_types'] ) ? $gutenberg['post_types'] : array();

		if ( in_array( $post_type, $exceptions, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Content post types that can use the classic/block editor toggle.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, string> Slug => label.
	 */
	private function content_post_types(): array {
		$objects = get_post_types( array( 'show_ui' => true ), 'objects' );
		$list    = array();

		foreach ( $objects as $object ) {
			if ( in_array( $object->name, self::SKIP_TYPES, true ) ) {
				continue;
			}

			if ( ! post_type_supports( $object->name, 'editor' ) ) {
				continue;
			}

			$list[ $object->name ] = $object->labels->singular_name;
		}

		return $list;
	}
}
