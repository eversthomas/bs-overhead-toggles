<?php
/**
 * Toggle module registry.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles;

defined( 'ABSPATH' ) || exit;

/**
 * Holds registered toggle modules and boots those that are enabled.
 *
 * @since 0.1.0
 */
final class Registry {

	public const CAT_PERFORMANCE = 'performance';
	public const CAT_PRIVACY     = 'privacy';
	public const CAT_CLEANUP     = 'cleanup';

	public const GROUP_FRONTEND_CLASSES = 'frontend_classes';
	public const GROUP_STORAGE          = 'storage';

	/**
	 * Registered modules keyed by id.
	 *
	 * @since 0.1.0
	 *
	 * @var array<string, Toggle>
	 */
	private array $modules = array();

	/**
	 * Adds a module. Duplicate ids are ignored.
	 *
	 * @since 0.1.0
	 *
	 * @param Toggle $toggle Module instance.
	 * @return void
	 */
	public function add( Toggle $toggle ): void {
		$id = $toggle->get_id();

		if ( isset( $this->modules[ $id ] ) ) {
			return;
		}

		$this->modules[ $id ] = $toggle;
	}

	/**
	 * All registered modules.
	 *
	 * @since 0.1.0
	 *
	 * @return Toggle[]
	 */
	public function all(): array {
		return array_values( $this->modules );
	}

	/**
	 * Single module by id.
	 *
	 * @since 0.1.0
	 *
	 * @param string $id Module id.
	 * @return Toggle|null
	 */
	public function get( string $id ): ?Toggle {
		return $this->modules[ $id ] ?? null;
	}

	/**
	 * Modules in a motivational category.
	 *
	 * @since 0.1.0
	 *
	 * @param string $category Category slug.
	 * @return Toggle[]
	 */
	public function by_category( string $category ): array {
		return array_values(
			array_filter(
				$this->modules,
				static fn( Toggle $toggle ): bool => $toggle->get_category() === $category
			)
		);
	}

	/**
	 * Category metadata for the settings tabs.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, array{label: string, description: string}>
	 */
	public static function categories(): array {
		return array(
			self::CAT_PERFORMANCE => array(
				'label'       => __( 'Performance', 'bs-overhead-toggles' ),
				'description' => __( 'Weniger Skripte und Styles, die jede Seite mitlädt.', 'bs-overhead-toggles' ),
			),
			self::CAT_PRIVACY     => array(
				'label'       => __( 'Sicherheit/Privacy', 'bs-overhead-toggles' ),
				'description' => __( 'Weniger öffentliche Schnittstellen und entblößende Hinweise im Quellcode.', 'bs-overhead-toggles' ),
			),
			self::CAT_CLEANUP     => array(
				'label'       => __( 'Aufräumen im Code', 'bs-overhead-toggles' ),
				'description' => __( 'Standard-Ausgaben und automatisch gesetzte Klassen entfernen.', 'bs-overhead-toggles' ),
			),
		);
	}

	/**
	 * Optional subgroup headings inside a category tab.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, array{label: string, description: string}>
	 */
	public static function groups(): array {
		return array(
			self::GROUP_FRONTEND_CLASSES => array(
				'label'       => __( 'Frontend-Klassen', 'bs-overhead-toggles' ),
				'description' => __( 'Automatisch gesetzte CSS-Klassen im HTML entfernen. Klassen, die du selbst im Theme oder Menü vergibst, bleiben.', 'bs-overhead-toggles' ),
			),
			self::GROUP_STORAGE          => array(
				'label'       => __( 'Speicherung und Verlauf', 'bs-overhead-toggles' ),
				'description' => __( 'Wie lange WordPress alte Fassungen und den Papierkorb behält.', 'bs-overhead-toggles' ),
			),
		);
	}

	/**
	 * Lets enabled, unlocked modules attach their hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function boot_enabled(): void {
		foreach ( $this->modules as $toggle ) {
			if ( $toggle->is_locked() || ! $toggle->is_enabled() ) {
				continue;
			}

			$toggle->register();
		}
	}
}
