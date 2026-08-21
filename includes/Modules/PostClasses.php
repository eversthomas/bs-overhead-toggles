<?php
/**
 * Strips noisy WordPress post classes from the frontend.
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
 * Keeps the post-type slug, sticky, thumbnail and password classes —
 * those are the ones themes actually style against.
 *
 * @since 0.1.0
 */
final class PostClasses extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'post_classes';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Beitrags-Klassen aufräumen', 'bs-overhead-toggles' );
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
		return __( 'Nimmt IDs, Status und Schlagwort-Klassen von Beitrags-Hüllen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Entfernt automatisch gesetzte Klassen wie Beitrags-ID, Status, Format und Kategorie am Artikel-Wrapper. Der Inhaltstyp (Beitrag, Seite) und Hinweise wie „angeheftet“ oder „hat Vorschaubild“ bleiben.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Weniger Klassen-Ballast in Listen und Einzelansichten, ohne die üblichen Theme-Anker zu kappen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Theme-CSS, das auf hentry, category-* oder format-* zielt, wirkt nicht mehr. Eigene Klassen, die das Theme an post_class() übergibt, bleiben.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'post_class priority 99; post_class_taxonomies → empty (keeps post-type slug, sticky, has-post-thumbnail, post-password-*)';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		add_filter( 'post_class_taxonomies', array( $this, 'filter_taxonomies' ), 99 );
		add_filter( 'post_class', array( $this, 'filter_classes' ), 99 );
	}

	/**
	 * Stops core from emitting per-term classes.
	 *
	 * @since 0.1.0
	 *
	 * @param string[] $taxonomies Taxonomy names.
	 * @return string[]
	 */
	public function filter_taxonomies( array $taxonomies ): array {
		if ( ! $this->is_frontend_output() ) {
			return $taxonomies;
		}

		return array();
	}

	/**
	 * Drops ID, type, status, format and hentry classes.
	 *
	 * @since 0.1.0
	 *
	 * @param string[] $classes Post class names.
	 * @return string[]
	 */
	public function filter_classes( array $classes ): array {
		if ( ! $this->is_frontend_output() ) {
			return $classes;
		}

		return HtmlClasses::filter_list(
			$classes,
			fn( string $class ): bool => $this->should_strip( $class )
		);
	}

	/**
	 * Whether a post class is WordPress bookkeeping.
	 *
	 * @since 0.1.0
	 *
	 * @param string $class Class name.
	 * @return bool
	 */
	private function should_strip( string $class ): bool {
		if ( 'hentry' === $class ) {
			return true;
		}

		return (bool) preg_match( '/^(post-\d+|type-|status-|format-)/', $class );
	}
}
