<?php
/**
 * Removes Dashicons on the frontend.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\Registry;

/**
 * Dashicons are a dependency of the admin bar; they typically load on the
 * frontend only when the toolbar is visible.
 *
 * @since 0.1.0
 */
final class Dashicons extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'dashicons';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Dashicons im Frontend entfernen', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_category(): string {
		return Registry::CAT_PERFORMANCE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description(): string {
		return __( 'Entfernt die WordPress-Icon-Schrift auf der öffentlichen Website.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Verhindert, dass die Icon-Schriftart „Dashicons“ im Frontend geladen wird. Im Backend bleibt sie erhalten.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Eine Schrift-Datei weniger. Spürbar vor allem, wenn die Admin-Leiste vorn sichtbar ist — dann lädt WordPress Dashicons sonst automatisch mit.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Icons in der Admin-Leiste und Themes oder Plugins, die Dashicons auf der Website nutzen, erscheinen als leere Kästchen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'wp_dequeue_style / wp_deregister_style( dashicons ) on wp_print_styles, frontend only';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		add_action( 'wp_print_styles', array( $this, 'dequeue' ), 100 );
	}

	/**
	 * Dequeues Dashicons after the admin bar has registered its dependency.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function dequeue(): void {
		if ( is_admin() ) {
			return;
		}

		wp_dequeue_style( 'dashicons' );
		wp_deregister_style( 'dashicons' );
	}
}
