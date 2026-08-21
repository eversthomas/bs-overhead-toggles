<?php
/**
 * Settings page template.
 *
 * Expected in scope: Registry $registry, bool $updated, bool $preset_applied, bool $preset_reset.
 *
 * @package BS\OverheadToggles
 */

defined( 'ABSPATH' ) || exit;

$categories      = \BS\OverheadToggles\Registry::categories();
$first_cat       = array_key_first( $categories );
$preset_applied  = $preset_applied ?? false;
$preset_reset    = $preset_reset ?? false;
$updated         = $updated ?? false;
?>
<div class="wrap bsot-wrap">
	<div
		class="bsot-app"
		data-i18n-on="<?php echo esc_attr__( 'Aktiv', 'bs-overhead-toggles' ); ?>"
		data-i18n-off="<?php echo esc_attr__( 'Inaktiv', 'bs-overhead-toggles' ); ?>"
	>
		<header class="bsot-page-head">
			<div>
				<h1><?php esc_html_e( 'BS Overhead Toggles', 'bs-overhead-toggles' ); ?></h1>
				<p class="bsot-page-lede">
					<?php esc_html_e( 'Schalte WordPress-Standardfunktionen ab, die du nicht brauchst — an einer Stelle, in Alltagssprache erklärt.', 'bs-overhead-toggles' ); ?>
				</p>
			</div>
		</header>

		<?php if ( $preset_applied ) : ?>
			<p class="bsot-confirm" role="status">
				<?php esc_html_e( 'Standard-Konfiguration angewendet. Du kannst einzelne Schalter noch anpassen.', 'bs-overhead-toggles' ); ?>
			</p>
		<?php elseif ( $preset_reset ) : ?>
			<p class="bsot-confirm" role="status">
				<?php esc_html_e( 'Alle Schalter sind wieder aus, soweit sie nicht in der wp-config.php stehen.', 'bs-overhead-toggles' ); ?>
			</p>
		<?php elseif ( $updated ) : ?>
			<p class="bsot-confirm" role="status">
				<?php esc_html_e( 'Einstellungen gespeichert.', 'bs-overhead-toggles' ); ?>
			</p>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bsot-setup">
			<input type="hidden" name="action" value="bsot_preset" />
			<?php wp_nonce_field( 'bsot_preset' ); ?>
			<div class="bsot-setup-actions">
				<button type="submit" name="bsot_preset" value="standard" class="bsot-btn bsot-btn-primary">
					<?php esc_html_e( 'Standard-Konfiguration anwenden', 'bs-overhead-toggles' ); ?>
				</button>
				<button
					type="submit"
					name="bsot_preset"
					value="reset"
					class="bsot-btn bsot-btn-danger"
					data-bsot-confirm="<?php echo esc_attr__( 'Alle Schalter auf Aus setzen? Werte aus der wp-config.php bleiben gesperrt.', 'bs-overhead-toggles' ); ?>"
				>
					<?php esc_html_e( 'Alles zurücksetzen', 'bs-overhead-toggles' ); ?>
				</button>
			</div>
			<p class="bsot-setup-hint">
				<?php esc_html_e( 'Schaltet den üblichen Overhead aus (Emojis, Block-CSS, Head-Hinweise, XML-RPC, Body-IDs). Gutenberg, Canonical, REST-Sperren, Speicherung und experimentelle Klassen bleiben aus. Bei Block-Themes das Frontend prüfen — Standard-Block-CSS fehlt dann.', 'bs-overhead-toggles' ); ?>
			</p>
		</form>

		<form action="options.php" method="post" class="bsot-form">
			<?php settings_fields( \BS\OverheadToggles\Settings::OPTION_GROUP ); ?>
			<input type="hidden" name="<?php echo esc_attr( \BS\OverheadToggles\Settings::OPTION_KEY ); ?>[schema_version]" value="<?php echo esc_attr( (string) \BS\OverheadToggles\Settings::SCHEMA_VERSION ); ?>" />

			<div class="bsot-layout">
				<div class="bsot-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Kategorien', 'bs-overhead-toggles' ); ?>">
					<?php foreach ( $categories as $slug => $meta ) : ?>
						<button
							type="button"
							class="bsot-tab"
							role="tab"
							id="bsot-tab-<?php echo esc_attr( $slug ); ?>"
							data-bsot-tab="<?php echo esc_attr( $slug ); ?>"
							aria-controls="bsot-panel-<?php echo esc_attr( $slug ); ?>"
							aria-selected="<?php echo $slug === $first_cat ? 'true' : 'false'; ?>"
						>
							<?php echo esc_html( $meta['label'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>

				<div class="bsot-panels">
					<?php foreach ( $categories as $slug => $meta ) : ?>
						<?php $modules = $registry->by_category( $slug ); ?>
						<section
							class="bsot-panel"
							role="tabpanel"
							id="bsot-panel-<?php echo esc_attr( $slug ); ?>"
							data-bsot-panel="<?php echo esc_attr( $slug ); ?>"
							aria-labelledby="bsot-tab-<?php echo esc_attr( $slug ); ?>"
							<?php echo $slug === $first_cat ? '' : 'hidden'; ?>
						>
							<div class="bsot-card">
								<div class="bsot-card-head">
									<h2><?php echo esc_html( $meta['label'] ); ?></h2>
									<p class="bsot-card-lede"><?php echo esc_html( $meta['description'] ); ?></p>
								</div>
								<div class="bsot-card-body">
									<?php if ( array() === $modules ) : ?>
										<p class="bsot-empty">
											<?php esc_html_e( 'In dieser Kategorie gibt es gerade keine Schalter.', 'bs-overhead-toggles' ); ?>
										</p>
									<?php else : ?>
										<?php
										$last_group = '';
										$groups     = \BS\OverheadToggles\Registry::groups();
										foreach ( $modules as $toggle ) :
											$group = $toggle->get_group();
											if ( '' !== $group && $group !== $last_group && isset( $groups[ $group ] ) ) :
												?>
												<div class="bsot-group">
													<h3 class="bsot-group-title"><?php echo esc_html( $groups[ $group ]['label'] ); ?></h3>
													<p class="bsot-group-lede"><?php echo esc_html( $groups[ $group ]['description'] ); ?></p>
												</div>
												<?php
											endif;
											$last_group = $group;
											$this->render_toggle_row( $toggle );
										endforeach;
										?>
									<?php endif; ?>
								</div>
							</div>
						</section>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="bsot-page-actions">
				<button type="submit" class="bsot-btn bsot-btn-primary">
					<?php esc_html_e( 'Einstellungen speichern', 'bs-overhead-toggles' ); ?>
				</button>
			</div>
		</form>
	</div>
</div>
