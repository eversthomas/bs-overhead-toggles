<?php
/**
 * Removes WordPress emoji scripts, styles, and CDN prefetch.
 *
 * @package BS\OverheadToggles
 */

declare( strict_types=1 );

namespace BS\OverheadToggles\Modules;

defined( 'ABSPATH' ) || exit;

use BS\OverheadToggles\AbstractToggle;
use BS\OverheadToggles\Registry;

/**
 * WordPress 7.0 prints the emoji loader as a script module in the footer
 * (and on embeds). Unhooking only `wp_head` is not enough if the detection
 * function has already queued `_print_emoji_detection_script`.
 *
 * @since 0.1.0
 */
final class Emojis extends AbstractToggle {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'emojis';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Emoji-Skript entfernen', 'bs-overhead-toggles' );
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
		return __( 'WordPress lädt auf jeder Seite extra Code, nur damit Emojis in alten Browsern gleich aussehen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_what_it_does(): string {
		return __( 'Entfernt das Emoji-Skript (seit WordPress 7.0 als Modul im Footer, plus die Variante in eingebetteten Beiträgen), die zugehörigen Styles und die Vorab-Verbindung zum Emoji-Server.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_benefit(): string {
		return __( 'Eine Datei und etwas JavaScript weniger auf jeder Seite. Emojis im Text bleiben — der Browser zeigt sie selbst an.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_breaks(): string {
		return __( 'Praktisch nie. Nur sehr alte Browser ohne eigene Emojis würden Platzhalter statt bunter Bilder sehen.', 'bs-overhead-toggles' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_developer_details(): string {
		return 'print_emoji_detection_script (wp_head, embed_head, admin_print_scripts); _print_emoji_detection_script (wp_print_footer_scripts); wp_enqueue_emoji_styles / print_emoji_styles; emoji_svg_url; tiny_mce_plugins: wpemoji';
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'embed_head', 'print_emoji_detection_script' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );

		remove_action( 'wp_print_footer_scripts', '_print_emoji_detection_script' );
		remove_action( 'admin_print_footer_scripts', '_print_emoji_detection_script' );

		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
		remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
		remove_action( 'enqueue_embed_scripts', 'wp_enqueue_emoji_styles' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );

		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		add_filter( 'tiny_mce_plugins', array( $this, 'strip_tinymce_plugin' ) );
		add_filter( 'emoji_svg_url', '__return_false' );
		add_filter( 'wp_resource_hints', array( $this, 'strip_emoji_hints' ), 10, 2 );
	}

	/**
	 * Drops the core TinyMCE emoji plugin.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $plugins TinyMCE plugin list.
	 * @return array<int, string>
	 */
	public function strip_tinymce_plugin( mixed $plugins ): array {
		if ( ! is_array( $plugins ) ) {
			return array();
		}

		return array_values( array_diff( $plugins, array( 'wpemoji' ) ) );
	}

	/**
	 * Removes emoji CDN hosts from resource hints.
	 *
	 * @since 0.1.0
	 *
	 * @param array<int, string|array<string, mixed>> $urls          Hint URLs.
	 * @param string                                  $relation_type Hint relation.
	 * @return array<int, string|array<string, mixed>>
	 */
	public function strip_emoji_hints( array $urls, string $relation_type ): array {
		if ( ! in_array( $relation_type, array( 'dns-prefetch', 'preconnect' ), true ) ) {
			return $urls;
		}

		return array_values(
			array_filter(
				$urls,
				static function ( $url ): bool {
					$href = is_array( $url ) ? (string) ( $url['href'] ?? '' ) : (string) $url;

					return ! str_contains( $href, 's.w.org/images/core/emoji' );
				}
			)
		);
	}
}
