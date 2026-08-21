<?php
/**
 * Settings page template.
 *
 * Expected in scope: Registry $registry, bool $updated, Settings $this via $this in Settings::render_page.
 *
 * @package BS\OverheadToggles
 */

defined( 'ABSPATH' ) || exit;

$categories = \BS\OverheadToggles\Registry::categories();
$first_cat  = array_key_first( $categories );
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

		<?php if ( $updated ) : ?>
			<p class="bsot-confirm" role="status">
				<?php esc_html_e( 'Einstellungen gespeichert.', 'bs-overhead-toggles' ); ?>
			</p>
		<?php endif; ?>

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
											<?php esc_html_e( 'In dieser Kategorie sind noch keine Schalter vorhanden. Sie erscheinen hier, sobald die zugehörigen Module ergänzt sind.', 'bs-overhead-toggles' ); ?>
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
