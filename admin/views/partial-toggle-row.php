<?php
/**
 * Single toggle row: switch, status, disclosure, dreier schema, lock warning.
 *
 * Expected in scope: \BS\OverheadToggles\Toggle $toggle
 *
 * @package BS\OverheadToggles
 */

defined( 'ABSPATH' ) || exit;

$id          = $toggle->get_id();
$enabled     = $toggle->is_enabled();
$locked      = $toggle->is_locked();
$breaks      = $toggle->get_breaks();
$dev         = $toggle->get_developer_details();
$option_name = \BS\OverheadToggles\Settings::OPTION_KEY . '[' . $id . ']';
$panel_id    = 'bsot-explain-' . $id;
$switch_id   = 'bsot-switch-' . $id;
?>
<div class="bsot-toggle<?php echo $locked ? ' is-locked' : ''; ?>">
	<div class="bsot-toggle-row">
		<button
			type="button"
			class="bsot-switch"
			role="switch"
			id="<?php echo esc_attr( $switch_id ); ?>"
			aria-checked="<?php echo $enabled ? 'true' : 'false'; ?>"
			aria-describedby="<?php echo esc_attr( $switch_id ); ?>-desc"
			<?php disabled( $locked ); ?>
			data-bsot-switch
		></button>
		<input
			type="hidden"
			name="<?php echo esc_attr( $option_name ); ?>"
			value="<?php echo $enabled ? '1' : '0'; ?>"
			<?php echo $locked ? 'disabled' : ''; ?>
			data-bsot-switch-value
		/>

		<div class="bsot-toggle-copy">
			<div class="bsot-toggle-label-row">
				<label class="bsot-toggle-label" for="<?php echo esc_attr( $switch_id ); ?>">
					<?php echo esc_html( $toggle->get_label() ); ?>
				</label>
				<?php if ( $toggle->is_experimental() ) : ?>
					<span class="bsot-badge bsot-badge-info"><?php esc_html_e( 'Experimentell', 'bs-overhead-toggles' ); ?></span>
				<?php endif; ?>
			</div>
			<p class="bsot-toggle-desc" id="<?php echo esc_attr( $switch_id ); ?>-desc">
				<?php echo esc_html( $toggle->get_description() ); ?>
			</p>
		</div>

		<span class="bsot-status <?php echo $enabled ? 'is-on' : 'is-off'; ?>" data-bsot-status>
			<?php echo $enabled ? esc_html__( 'Aktiv', 'bs-overhead-toggles' ) : esc_html__( 'Inaktiv', 'bs-overhead-toggles' ); ?>
		</span>

		<button
			type="button"
			class="bsot-more"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $panel_id ); ?>"
			data-bsot-more
		>
			<?php esc_html_e( 'Mehr erfahren', 'bs-overhead-toggles' ); ?>
		</button>
	</div>

	<?php if ( $locked ) : ?>
		<div class="bsot-warn" role="note">
			<p class="bsot-warn-label">
				<span class="bsot-warn-icon" aria-hidden="true">⚠</span>
				<?php esc_html_e( 'Wird von außen vorgegeben', 'bs-overhead-toggles' ); ?>
			</p>
			<p><?php echo esc_html( $toggle->get_lock_reason() ); ?></p>
		</div>
	<?php endif; ?>

	<div class="bsot-explain" id="<?php echo esc_attr( $panel_id ); ?>" hidden>
		<div class="bsot-explain-block">
			<h3><?php esc_html_e( 'Was macht\'s', 'bs-overhead-toggles' ); ?></h3>
			<p><?php echo esc_html( $toggle->get_what_it_does() ); ?></p>
		</div>
		<div class="bsot-explain-block">
			<h3><?php esc_html_e( 'Nutzen', 'bs-overhead-toggles' ); ?></h3>
			<p><?php echo esc_html( $toggle->get_benefit() ); ?></p>
		</div>
		<?php if ( '' !== $breaks ) : ?>
			<div class="bsot-warn" role="note">
				<p class="bsot-warn-label">
					<span class="bsot-warn-icon" aria-hidden="true">⚠</span>
					<?php esc_html_e( 'Bricht es was', 'bs-overhead-toggles' ); ?>
				</p>
				<p><?php echo esc_html( $breaks ); ?></p>
			</div>
		<?php endif; ?>
		<?php if ( '' !== $dev ) : ?>
			<details class="bsot-dev">
				<summary><?php esc_html_e( 'Für Entwickler', 'bs-overhead-toggles' ); ?></summary>
				<p><?php echo esc_html( $dev ); ?></p>
			</details>
		<?php endif; ?>
	</div>
</div>
