<?php
/**
 * Plugin bootstrap and composition root.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles;

defined( 'ABSPATH' ) || exit;

/**
 * Single entry point after autoloading is in place.
 *
 * @since 0.1.0
 */
final class Plugin {

	/**
	 * Option key for the stored plugin version.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public const VERSION_OPTION = 'bsot_version';

	/**
	 * Singleton instance.
	 *
	 * @since 0.1.0
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Toggle registry.
	 *
	 * @since 0.1.0
	 *
	 * @var Registry|null
	 */
	private ?Registry $registry = null;

	/**
	 * Settings controller.
	 *
	 * @since 0.1.0
	 *
	 * @var Settings|null
	 */
	private ?Settings $settings = null;

	/**
	 * Returns the shared plugin instance.
	 *
	 * @since 0.1.0
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Prevents external construction.
	 *
	 * @since 0.1.0
	 */
	private function __construct() {}

	/**
	 * Prevents cloning.
	 *
	 * @since 0.1.0
	 */
	private function __clone() {}

	/**
	 * Prevents unserialization.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function __wakeup(): void {
		throw new \LogicException( 'Cannot unserialize ' . self::class );
	}

	/**
	 * Runs on plugin activation.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function activate(): void {
		if ( false === get_option( self::VERSION_OPTION, false ) ) {
			add_option( self::VERSION_OPTION, BSOT_VERSION, '', false );
		} else {
			update_option( self::VERSION_OPTION, BSOT_VERSION, false );
		}

		if ( false === get_option( Settings::OPTION_KEY, false ) ) {
			add_option( Settings::OPTION_KEY, Settings::defaults(), '', false );
		}
	}

	/**
	 * Runs on plugin deactivation.
	 *
	 * Options are left in place; deletion belongs in uninstall.php (later).
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		// Intentionally empty.
	}

	/**
	 * Wires plugin hooks.
	 *
	 * ConstantLock::capture() MUST stay first: later phases may define
	 * AUTOSAVE_INTERVAL / EMPTY_TRASH_DAYS from options, which would make a
	 * late defined() check indistinguishable from a wp-config.php lock.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function boot(): void {
		ConstantLock::capture();

		$this->registry = new Registry();
		$this->register_modules();
		$this->settings = new Settings( $this->registry );
		$this->settings->hooks();

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'boot_modules' ), 5 );
	}

	/**
	 * Toggle registry (empty until Phase 2+ modules are added).
	 *
	 * @since 0.1.0
	 *
	 * @return Registry
	 */
	public function registry(): Registry {
		if ( null === $this->registry ) {
			$this->registry = new Registry();
		}

		return $this->registry;
	}

	/**
	 * Instantiates toggle modules and adds them to the registry.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	private function register_modules(): void {
		$this->registry->add( new Modules\Emojis() );
		$this->registry->add( new Modules\BlockLibraryCss() );
		$this->registry->add( new Modules\GlobalStyles() );
		$this->registry->add( new Modules\ImgAutoSizes() );
		$this->registry->add( new Modules\Dashicons() );
		$this->registry->add( new Modules\OEmbed() );
		$this->registry->add( new Modules\FeedLinks() );
		$this->registry->add( new Modules\Canonical() );
		$this->registry->add( new Modules\Shortlink() );
		$this->registry->add( new Modules\Generator() );
		$this->registry->add( new Modules\Rsd() );
		$this->registry->add( new Modules\Gutenberg() );
		$this->registry->add( new Modules\ClassicWidgets() );
		$this->registry->add( new Modules\RestDiscovery() );
		$this->registry->add( new Modules\RestUsers() );
		$this->registry->add( new Modules\RestGuests() );
		$this->registry->add( new Modules\BodyClasses() );
		$this->registry->add( new Modules\PostClasses() );
		$this->registry->add( new Modules\NavClasses() );
		$this->registry->add( new Modules\ImageClasses() );
		$this->registry->add( new Modules\BlockClasses() );

		/**
		 * Fires when toggle modules may be added to the registry.
		 *
		 * @since 0.1.0
		 *
		 * @param Registry $registry Module registry.
		 */
		do_action( 'bsot_register_modules', $this->registry );
	}

	/**
	 * Lets enabled modules attach their WordPress hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function boot_modules(): void {
		$this->registry()->boot_enabled();
	}

	/**
	 * Loads translations from /languages.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'bs-overhead-toggles',
			false,
			dirname( BSOT_BASENAME ) . '/languages'
		);
	}
}
