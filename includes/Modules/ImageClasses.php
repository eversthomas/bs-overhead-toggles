<?php
/**
 * Strips WordPress image, caption and gallery classes from frontend HTML.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\HtmlClasses;
use BS\OverheadToggles\Registry;

/**
 * Alignment classes (alignleft / aligncenter / …) stay — they affect layout.
 * wp-post-image stays for featured-image styling.
 *
 * @since 0.1.0
 */
final class ImageClasses extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'image_classes';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Bild- und Galerie-Klassen aufräumen', 'bs-overhead-toggles' );
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
		return __( 'Nimmt wp-image-*, Größen- und Galerie-Klassen aus dem Inhalt.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Entfernt die von WordPress gesetzten Klassen an Bildern, Bildunterschriften und Galerien. Ausrichtungsklassen (links/rechts/mittig) bleiben, damit das Layout hält.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Kürzeres Inhalts-HTML ohne Anhangs-IDs und Größen-Namen an jedem Bild.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Andere Plugins und Skripte hängen oft an genau diesen Klassen. Typisch: Lightbox-Skripte über .wp-image-*. Galerie- und Caption-CSS von WordPress oder dem Theme kann ebenfalls wegfallen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'wp_get_attachment_image_attributes, get_image_tag_class, render_block/the_content/widget_* via WP_HTML_Tag_Processor; keeps align*, wp-post-image';
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
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'filter_attachment_attributes' ), 99 );
		add_filter( 'get_image_tag_class', array( $this, 'filter_image_tag_class' ), 99 );
		add_filter( 'render_block', array( $this, 'filter_html' ), 20 );
		add_filter( 'the_content', array( $this, 'filter_html' ), 20 );
		add_filter( 'widget_text_content', array( $this, 'filter_html' ), 20 );
		add_filter( 'widget_block_content', array( $this, 'filter_html' ), 20 );
	}

	/**
	 * Strips matching classes from attachment image attributes.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $attr Image attributes.
	 * @return array<string, mixed>
	 */
	public function filter_attachment_attributes( array $attr ): array {
		if ( ! $this->is_frontend_output() || empty( $attr['class'] ) || ! is_string( $attr['class'] ) ) {
			return $attr;
		}

		$classes = preg_split( '/\s+/', $attr['class'], -1, PREG_SPLIT_NO_EMPTY ) ?: array();
		$attr['class'] = implode( ' ', $this->filter_names( $classes ) );

		return $attr;
	}

	/**
	 * Strips matching classes from get_image_tag().
	 *
	 * @since 0.1.0
	 *
	 * @param string $class Space-separated class names.
	 * @return string
	 */
	public function filter_image_tag_class( string $class ): string {
		if ( ! $this->is_frontend_output() ) {
			return $class;
		}

		$classes = preg_split( '/\s+/', $class, -1, PREG_SPLIT_NO_EMPTY ) ?: array();

		return implode( ' ', $this->filter_names( $classes ) );
	}

	/**
	 * Strips matching classes from an HTML fragment.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $html HTML or other filter payload.
	 * @return mixed
	 */
	public function filter_html( mixed $html ): mixed {
		if ( ! is_string( $html ) || '' === $html || ! $this->is_frontend_output() ) {
			return $html;
		}

		return HtmlClasses::strip(
			$html,
			fn( string $class ): bool => $this->should_strip( $class ),
			array( 'img', 'figure', 'figcaption', 'p', 'div', 'dl', 'dt', 'dd', 'ul', 'li', 'a' )
		);
	}

	/**
	 * Filters a class list.
	 *
	 * @since 0.1.0
	 *
	 * @param string[] $classes Class names.
	 * @return string[]
	 */
	private function filter_names( array $classes ): array {
		return HtmlClasses::filter_list(
			$classes,
			fn( string $class ): bool => $this->should_strip( $class )
		);
	}

	/**
	 * Whether an image/caption/gallery class should be removed.
	 *
	 * @since 0.1.0
	 *
	 * @param string $class Class name.
	 * @return bool
	 */
	private function should_strip( string $class ): bool {
		if ( in_array(
			$class,
			array(
				'wp-caption',
				'wp-caption-text',
				'wp-element-caption',
				'gallery',
				'gallery-item',
				'gallery-caption',
				'gallery-icon',
			),
			true
		) ) {
			return true;
		}

		if ( preg_match( '/^(wp-image-\d+|galleryid-\d+|gallery-columns-\d+|gallery-size-)/', $class ) ) {
			return true;
		}

		return $this->is_size_class( $class );
	}

	/**
	 * Whether the class is a registered image size name (size-* / attachment-*).
	 *
	 * @since 0.1.0
	 *
	 * @param string $class Class name.
	 * @return bool
	 */
	private function is_size_class( string $class ): bool {
		if ( preg_match( '/^(size|attachment)-\d+x\d+$/', $class ) ) {
			return true;
		}

		$sizes = array_merge( array( 'full', 'custom' ), get_intermediate_image_sizes() );

		foreach ( $sizes as $size ) {
			$size = (string) $size;
			if ( 'size-' . $size === $class || 'attachment-' . $size === $class ) {
				return true;
			}
		}

		return false;
	}
}
