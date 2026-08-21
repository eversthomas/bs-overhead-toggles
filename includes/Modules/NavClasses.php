<?php
/**
 * Strips verbose WordPress nav-menu classes from the frontend.
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
 * Keeps current-menu-* and menu-item-has-children so typical theme
 * highlighting still works. Custom classes from the menu UI stay.
 *
 * @since 0.1.0
 */
final class NavClasses extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'nav_classes';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Menü-Klassen aufräumen', 'bs-overhead-toggles' );
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
		return __( 'Nimmt Typ-, Objekt- und ID-Klassen von Navigationspunkten.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Entfernt die langen Standardklassen an Menüpunkten (Typ, Objekt, ID) und die id="menu-item-…"-Attribute. Die Markierung des aktuellen Punkts und „hat Unterpunkte“ bleiben, ebenso Klassen, die du im Menü selbst setzt.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Deutlich kürzere Menü-Listen im HTML, ohne dass aktive Punkte unsichtbar werden.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Theme-CSS, das Menüpunkte über menu-item-type-* oder page-item-* stylt, greift nicht mehr. current-menu-item bleibt als Anker für den aktiven Punkt.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'nav_menu_css_class, nav_menu_item_id; keeps current-menu-*, menu-item, menu-item-has-children, menu-item-home';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		add_filter( 'nav_menu_css_class', array( $this, 'filter_classes' ), 99 );
		add_filter( 'nav_menu_item_id', array( $this, 'filter_item_id' ), 99 );
	}

	/**
	 * Drops verbose generated menu classes.
	 *
	 * @since 0.1.0
	 *
	 * @param string[] $classes Menu item class names.
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
	 * Removes the generated menu-item-{id} HTML id.
	 *
	 * @since 0.1.0
	 *
	 * @param string $id Menu item HTML id.
	 * @return string
	 */
	public function filter_item_id( string $id ): string {
		if ( ! $this->is_frontend_output() ) {
			return $id;
		}

		return '';
	}

	/**
	 * Whether a nav class is WordPress bookkeeping.
	 *
	 * @since 0.1.0
	 *
	 * @param string $class Class name.
	 * @return bool
	 */
	private function should_strip( string $class ): bool {
		if ( in_array( $class, array( 'page_item', 'current_page_item', 'current_page_parent', 'current_page_ancestor' ), true ) ) {
			return true;
		}

		return (bool) preg_match( '/^(menu-item-\d+|menu-item-type-|menu-item-object-|page-item-)/', $class );
	}
}
