<?php
/**
 * Contract for a single overhead-toggle module.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles;

defined( 'ABSPATH' ) || exit;

/**
 * Every toggle is a self-contained module implementing this interface.
 *
 * A new toggle is a new class plus a Registry entry — Settings and core
 * bootstrap stay unchanged.
 *
 * @since 0.1.0
 */
interface Toggle {

	/**
	 * Stable option key, e.g. `emojis`.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_id(): string;

	/**
	 * UI label in everyday language.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_label(): string;

	/**
	 * Motivational category slug: performance, privacy, or cleanup.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_category(): string;

	/**
	 * Optional subgroup slug within the category (empty = ungrouped).
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_group(): string;

	/**
	 * One-line summary shown on the toggle row (not the full explanation).
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_description(): string;

	/**
	 * „Was macht's" — technical effect in everyday language.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_what_it_does(): string;

	/**
	 * „Nutzen" — why turning this off helps.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_benefit(): string;

	/**
	 * „Bricht es was" — warning text, empty if nothing breaks.
	 *
	 * Matches the plan's `get_impact_text()`.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_breaks(): string;

	/**
	 * Collapsed „Für Entwickler" detail (hooks, option keys). Empty if unused.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_developer_details(): string;

	/**
	 * Whether the toggle is marked experimental in the UI.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function is_experimental(): bool;

	/**
	 * Whether the stored option currently enables this module.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function is_enabled(): bool;

	/**
	 * Whether wp-config.php (or another early define) already locked this setting.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function is_locked(): bool;

	/**
	 * Everyday-language reason shown when `is_locked()` is true.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_lock_reason(): string;

	/**
	 * Default stored value (bool or nested array for granular toggles).
	 *
	 * @since 0.1.0
	 *
	 * @return mixed
	 */
	public function get_default(): mixed;

	/**
	 * Sanitizes a submitted value for this module.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $value Raw submitted value.
	 * @return mixed
	 */
	public function sanitize( mixed $value ): mixed;

	/**
	 * Renders the hidden input the switch writes to.
	 *
	 * @since 0.1.0
	 *
	 * @param bool $enabled Current enabled state.
	 * @param bool $locked  Whether the control is locked.
	 * @return void
	 */
	public function render_switch_input( bool $enabled, bool $locked ): void;

	/**
	 * Optional extra form controls under the toggle row (post types, whitelists).
	 *
	 * @since 0.1.0
	 *
	 * @param bool $locked Whether the control is locked.
	 * @return void
	 */
	public function render_extra_fields( bool $locked ): void;

	/**
	 * Whether the „Bricht es was"-Warnkasten is shown on the row, not only when expanded.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function show_warning_inline(): bool;

	/**
	 * Hooks the module into WordPress when it is enabled and not locked.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register(): void;

	/**
	 * Runs on `plugins_loaded`, before core defines functionality constants.
	 *
	 * Used by modules that must `define()` AUTOSAVE_INTERVAL or EMPTY_TRASH_DAYS.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function boot_early(): void;

	/**
	 * Whether this feature is effectively disabled for an optional context.
	 *
	 * Context is feature-specific (post type, heartbeat screen, …). Null means
	 * “is the module active at all”. Locked or inactive modules return false.
	 *
	 * @since 0.1.0
	 *
	 * @param string|null $context Optional sub-context.
	 * @return bool
	 */
	public function is_disabled_in_context( ?string $context ): bool;
}
