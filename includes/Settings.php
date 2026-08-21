<?php
/**
 * Settings API registration and options page.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles;

defined( 'ABSPATH' ) || exit;

/**
 * Registers `bsot_options`, the admin page, and renders toggle rows.
 *
 * @since 0.1.0
 */
final class Settings {

	public const OPTION_KEY     = 'bsot_options';
	public const OPTION_GROUP   = 'bsot_settings';
	public const PAGE_SLUG      = 'bs-overhead-toggles';
	public const SCHEMA_VERSION = 1;

	/**
	 * Menu position just below Dashboard (2), before the first separator (4).
	 *
	 * Fractional string avoids colliding with other plugins at the same slot
	 * and keeps the mantissa intact (WP uses the value as an array key).
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public const MENU_POSITION = '3.001';

	/**
	 * Module registry.
	 *
	 * @since 0.1.0
	 *
	 * @var Registry
	 */
	private Registry $registry;

	/**
	 * @since 0.1.0
	 *
	 * @param Registry $registry Module registry.
	 */
	public function __construct( Registry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Hooks admin-only Settings API callbacks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'admin_menu', array( $this, 'register_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'plugin_action_links_' . BSOT_BASENAME, array( $this, 'action_links' ) );
	}

	/**
	 * Default option payload.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		return array(
			'schema_version' => self::SCHEMA_VERSION,
		);
	}

	/**
	 * Stored options as an array.
	 *
	 * Always `get_option()` — no site IDs, so a later Multisite layer can wrap this.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, mixed>
	 */
	public static function get_all(): array {
		$value = get_option( self::OPTION_KEY, array() );

		return is_array( $value ) ? $value : array();
	}

	/**
	 * Registers the option with the Settings API.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_setting(): void {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'capability'        => 'manage_options',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::defaults(),
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Sanitizes the submitted options array.
	 *
	 * Missing checkbox keys are treated as false. Unknown keys are dropped.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $input Raw POST value.
	 * @return array<string, mixed>
	 */
	public function sanitize( mixed $input ): array {
		if ( ! current_user_can( 'manage_options' ) ) {
			return self::get_all();
		}

		$submitted = is_array( $input ) ? $input : array();
		$clean     = self::defaults();

		$stored = self::get_all();

		foreach ( $this->registry->all() as $toggle ) {
			$id = $toggle->get_id();

			if ( $toggle->is_locked() ) {
				$clean[ $id ] = array_key_exists( $id, $stored ) ? $stored[ $id ] : $toggle->get_default();
				continue;
			}

			$raw          = array_key_exists( $id, $submitted ) ? $submitted[ $id ] : $toggle->get_default();
			$clean[ $id ] = $toggle->sanitize( $raw );
		}

		return $clean;
	}

	/**
	 * Adds a top-level menu item just below the Dashboard.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_page(): void {
		add_menu_page(
			__( 'BS Overhead Toggles', 'bs-overhead-toggles' ),
			__( 'Overhead', 'bs-overhead-toggles' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' ),
			$this->menu_icon(),
			self::MENU_POSITION
		);
	}

	/**
	 * Admin URL of this plugin's settings screen.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public static function page_url(): string {
		return admin_url( 'admin.php?page=' . self::PAGE_SLUG );
	}

	/**
	 * Base64 SVG: two toggle pills, matching the settings UI (monochrome for WP's menu mask).
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	private function menu_icon(): string {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="black"><path d="M5 3.5h10a3.5 3.5 0 1 1 0 7H5a3.5 3.5 0 1 1 0-7zm10 1.75a1.75 1.75 0 1 0 0 3.5 1.75 1.75 0 0 0 0-3.5zM5 10.5h10a3.5 3.5 0 1 1 0 7H5a3.5 3.5 0 1 1 0-7zm3.5 1.75a1.75 1.75 0 1 0 0 3.5 1.75 1.75 0 0 0 0-3.5z"/></svg>';

		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Enqueues CSS/JS on this plugin's settings page only.
	 *
	 * @since 0.1.0
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		if ( 'toplevel_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'bsot-admin',
			BSOT_URL . 'admin/assets/admin.css',
			array(),
			BSOT_VERSION
		);

		wp_enqueue_script(
			'bsot-admin',
			BSOT_URL . 'admin/assets/admin.js',
			array(),
			BSOT_VERSION,
			true
		);
	}

	/**
	 * Adds a settings link on the Plugins screen.
	 *
	 * @since 0.1.0
	 *
	 * @param string[] $links Existing action links.
	 * @return string[]
	 */
	public function action_links( array $links ): array {
		$url = self::page_url();

		array_unshift(
			$links,
			'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Einstellungen', 'bs-overhead-toggles' ) . '</a>'
		);

		return $links;
	}

	/**
	 * Renders the options page.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$registry   = $this->registry;
		$updated    = isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$view       = BSOT_DIR . 'admin/views/page-settings.php';

		include $view;
	}

	/**
	 * Renders one toggle row (used once modules exist).
	 *
	 * @since 0.1.0
	 *
	 * @param Toggle $toggle Module.
	 * @return void
	 */
	public function render_toggle_row( Toggle $toggle ): void {
		include BSOT_DIR . 'admin/views/partial-toggle-row.php';
	}
}
