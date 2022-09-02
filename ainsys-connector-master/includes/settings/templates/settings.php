<?php

namespace Ainsys\Connector\Master\Settings;

/**
 * Simplified reference to class which linked this template.
 *
 * @var Admin_UI;
 */
$admin_ui = $this;

try {
	$status = $admin_ui->is_ainsys_integration_active( 'check' );
} catch ( \Exception $e ) {
	echo esc_html( $e->getMessage() );
}

?>
<div class="wrap ainsys_settings_wrap">
	<h1><img src="<?php echo AINSYS_CONNECTOR_URL; ?>/assets/img/logo.svg" alt="Ainsys logo" class="ainsys-logo"></h1>

	<div class="nav-tab-wrapper ainsys-nav-tab-wrapper">
		<a class="nav-tab nav-tab-active" href="#setting_section_general" data-target="setting_section_general"><?php _e( 'General', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></a>
		<a class="nav-tab" href="#setting_section_log" data-target="setting_section_log"><?php _e( 'Transfer log', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></a>
		<a class="nav-tab" href="#setting_entities_section" data-target="setting_entities_section"><?php _e( 'Entities export settings', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></a>
	</div>

	<div id="setting_section_general" class="tab-target nav-tab-active tab-target-active">
		<div class="ainsys-settings-blocks">
			<div class="ainsys-settings-block ainsys-settings-block--connection">
				<h2><?php _e( 'Connection Settings', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></h2>

				<form method="post" action="options.php">
					<?php settings_fields( $admin_ui->settings::get_option_name( 'group' ) ); ?>
					<div class="aisys-form-group">
						<label for="ansys-api-key" class="aisys-form-label">
							<?php _e( 'AINSYS handshake url for the connector. You can find it in your ', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
							<a href="https://app.ainsys.com/en/settings/workspaces" target="_blank">
								<?php _e( 'dashboard', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
							</a>.
						</label>
						<div class="aisys-form-input">
							<input id="ansys-api-key" type="text" size="50" name="<?php echo esc_html( $admin_ui->settings::get_option_name( 'ansys_api_key' ) ); ?>" placeholder="XXXXXXXXXXXXXXXXXXXXX" value="<?php echo esc_html( $admin_ui->settings::get_option( 'ansys_api_key' ) ); ?>"/>
						</div>
					</div>
					<div class="aisys-form-group">
						<label for="hook-url" class="aisys-form-label">
							<?php _e( 'Server hook_url', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
						</label>
						<div class="aisys-form-input">
							<input id="hook-url" type="text" size="50" name="<?php echo esc_attr( $admin_ui->settings::get_option_name( 'hook_url' ) ); ?>" value="<?php echo esc_attr( $admin_ui->settings::get_option( 'hook_url' ) ); ?>" disabled/>
						</div>
					</div>
					<div class="aisys-form-group">
						<label for="backup-email" class="aisys-form-label">
							<?php _e( 'Additional e-mail', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
							<span><?php _e( 'Used for error reports', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></span>
						</label>
						<div class="aisys-form-input">
							<input id="backup-email" type="text" name="<?php echo esc_attr( $admin_ui->settings::get_option_name( 'backup_email' ) ); ?>" placeholder="backup@email.com" value="<?php echo esc_attr( $admin_ui->settings::get_backup_email() ); ?>"/>
						</div>
					</div>
					<div class="aisys-form-group aisys-form-group-checkbox">
						<div class="aisys-form-input">
							<input id="full-uninstall-checkbox" type="checkbox" name="<?php echo esc_attr( $admin_ui->settings::get_option_name( 'full_uninstall' ) ); ?>" value="<?php echo esc_attr( $admin_ui->settings::get_option( 'full_uninstall' ) ); ?>" <?php checked( 1, esc_html( $admin_ui->settings::get_option( 'full_uninstall' ) ), true ); ?> />
						</div>
						<label for="full-uninstall-checkbox" class="aisys-form-label">
							<?php _e( 'Purge all stored data during deactivation ', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
							<div class="aisys-form-label-note"><?php _e( 'NB: if you delete the plugin from WordPress admin panel it will clear data regardless of this checkbox', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></div>
						</label>
					</div>
					<div class="aisys-form-group">
						<label for="connector-id" class="aisys-form-label">
							<?php _e( 'Connector Id', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
						</label>
						<div class="aisys-form-input">
							<input id="connector-id" type="text" size="50" name="<?php echo esc_attr( $admin_ui->settings::get_option_name( 'connector_id' ) ); ?>" value="<?php echo esc_attr( $admin_ui->settings::get_option( 'connector_id' ) ); ?>" />
						</div>
					</div>

					<div class="aisys-form-group-title"><h3><?php _e( 'Your Data', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></h3></div>
					<div class="ainsys-tabs">
						<div class="ainsys-tabs-nav">
							<a class="ainsys-nav-tab ainsys-nav-tab-active" href="#setting_section_individual" data-target="setting_section_individual"><?php _e( 'Individual', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></a>
							<a class="ainsys-nav-tab" href="#setting_section_organization" data-target="setting_section_organization"><?php _e( 'Legal Entity', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></a>
						</div>
						<div class="ainsys-tabs-target">
							<div id="setting_section_individual" class="ainsys-tab-target ainsys-tab-target-active">
								<div class="aisys-form-group">
									<label for="client-full-name" class="aisys-form-label">
										<?php _e( 'Full Name', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
									</label>
									<div class="aisys-form-input">
										<input id="client-full-name" type="text" size="50" name="<?php echo esc_attr( $admin_ui->settings::get_option_name( 'client_full_name' ) ); ?>" value="<?php echo esc_attr( $admin_ui->settings::get_option( 'client_full_name' ) ); ?>" />
									</div>
								</div>
							</div>
							<div id="setting_section_organization" class="ainsys-tab-target">
								<div class="aisys-form-group">
									<label for="client-company-name" class="aisys-form-label">
										<?php _e( 'Company Name', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
									</label>
									<div class="aisys-form-input">
										<input id="client-company-name" type="text" size="50" name="<?php echo esc_attr( $admin_ui->settings::get_option_name( 'client_company_name' ) ); ?>" value="<?php echo esc_attr( $admin_ui->settings::get_option( 'client_company_name' ) ); ?>" />
									</div>
								</div>
								<div class="aisys-form-group">
									<label for="client_tin" class="aisys-form-label">
										<?php _e( 'TIN', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
									</label>
									<div class="aisys-form-input">
										<input id="client_tin" type="text" size="50" name="<?php echo esc_attr( $admin_ui->settings::get_option_name( 'client_tin' ) ); ?>" value="<?php echo esc_attr( $admin_ui->settings::get_option( 'client_tin' ) ); ?>" />
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="submit">
						<input type="submit" class="btn btn-primary" value="<?php
							if ( ! empty( $status ) && 'success' === $status['status'] ) {
								_e( 'Save', AINSYS_CONNECTOR_TEXTDOMAIN );
							} else {
								_e( 'Connect', AINSYS_CONNECTOR_TEXTDOMAIN );
							}
							?>"/>
						<?php if ( ! empty( $status ) && 'success' === $status['status'] ) { ?>
							<a id="remove_ainsys_integration" class="btn btn-secondary"><?php _e( 'Disconect integration', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></a>
						<?php } ?>
					</div>
				</form>

			</div>

			<div class="ainsys-settings-block ainsys-settings-block--status">
				<?php
				$ainsys_status_items = apply_filters( 'ainsys_status_list', array() );

				$ainsys_status_items['curl'] = array(
					'title'  => 'CURL',
					'active' => extension_loaded( 'curl' ),
				);
				$ainsys_status_items['ssl']  = array(
					'title'  => 'SSL',
					'active' => \is_ssl(),
				);

				?>

				<h2><?php _e( 'Connection Status', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></h2>
				<ul class="ainsys-status-items">
					<li class="ainsys-li-underline">
						<span class="ainsys-status-title"><?php _e( 'Conection', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></span>
						<?php
						if ( ! empty( $status ) && 'success' === $status['status'] ) :
							?>
							<span class="ainsys-status-ok">
								<i class="fa fa-check-circle-o" aria-hidden="true"></i> <?php _e( 'Working', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
							</span>
							<?php
						else :
							?>
							<span class="ainsys-status-error">
								<i class="fa fa-times-circle-o" aria-hidden="true"></i> <?php _e( 'No AINSYS integration', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
							</span>
							<?php
						endif;
						?>
					</li>

					<?php foreach ( $ainsys_status_items as $status_key => &$status_item ) : ?>
						<li>
							<span class="ainsys-status-title"><?php echo esc_html( $status_item['title'] ); ?></span>
							<?php if ( $status_item['active'] ) : ?>
								<span class="ainsys-status-ok">
									<i class="fa fa-check-circle-o" aria-hidden="true"></i> <?php _e( 'Enabled', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
								</span>
							<?php else : ?>
								<span class="ainsys-status-error">
									<i class="fa fa-times-circle-o" aria-hidden="true"></i> <?php _e( 'Disabled', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
								</span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>

					<li class="ainsys-li-overline">
						<span class="ainsys-status-title"><?php _e( 'PHP version 7.2+', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></span>
						<?php
						if ( version_compare( PHP_VERSION, '7.2.0' ) > 0 ) :
							?>
							<span class="ainsys-status-ok"><i class="fa fa-check-circle-o" aria-hidden="true"></i> PHP <?php echo esc_html( PHP_VERSION ); ?></span>
						<?php else : ?>
							<span class="ainsys-status-error">
								<i class="fa fa-times-circle-o" aria-hidden="true"></i> <?php _e( 'Bad PHP version ', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>(
								<?php echo esc_html( PHP_VERSION ); ?>
								).
								<?php _e( 'Update on your hosting', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
							</span>
						<?php endif; ?>
					</li>
					<li>
						<span class="ainsys-status-title"><?php _e( 'Backup email', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></span>
						<?php
						if ( ! empty( $admin_ui->settings::get_backup_email() ) && filter_var( $admin_ui->settings::get_backup_email(), FILTER_VALIDATE_EMAIL ) ) :
							?>
							<span class="ainsys-status-ok">
								<i class="fa fa-check-circle-o" aria-hidden="true"></i> <?php _e( 'Valid', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
							</span>
						<?php else : ?>
							<span class="ainsys-status-error">
								<i class="fa fa-times-circle-o" aria-hidden="true"></i> <?php _e( 'Invalid', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
							</span>
						<?php endif; ?>
					</li>
				</ul>

			</div>
		</div>

	</div>

	<div id="setting_section_log" class="tab-target">
		<?php
		$start = $admin_ui->settings::get_option( 'do_log_transactions' ) ? ' disabled' : '';
		$stop  = $admin_ui->settings::get_option( 'do_log_transactions' ) ? '' : ' disabled';
		?>
		<div class="ainsys-log-block">
			<div class="ainsys-log-status">
				<span class="ainsys-log-status-title">Log Status: </span>
				<?php if ( $admin_ui->settings::get_option( 'do_log_transactions' ) ) { ?>
					<span class="ainsys-log-status-ok"><i class="fa fa-check-circle-o" aria-hidden="true"></i> Working</span>
				<?php } else { ?>
					<span class="ainsys-log-status-no"><i class="fa fa-times-circle-o" aria-hidden="true"></i> Not Working</span>
				<?php } ?>
			</div>
			<div class="ainsys-log-controls">
				<a id="start_loging" class="btn btn-primary ainsys-log-control<?php echo esc_attr( $start ); ?>"><?php _e( 'Start loging', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></a>
				<select id="start_loging_timeinterval" class="<?php echo esc_attr( $start ); ?>" name="loging_timeinterval">
					<option value="1"><?php _e( '1 hour', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></option>
					<option value="5"><?php _e( '5 hours', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></option>
					<option value="12"><?php _e( '12 hours', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></option>
					<option value="24"><?php _e( '24 hours', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></option>
					<option value="-1" selected="selected"><?php _e( 'unlimited', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></option>
				</select>
				<a id="stop_loging" class="btn btn-primary ainsys-log-control<?php echo esc_attr( $stop ); ?>"><?php _e( 'Stop loging', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></a>
				<a id="reload_log" class="btn btn-primary"><?php _e( 'Reload', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></a>
				<a id="clear_log" class="btn btn-primary"><?php _e( 'Clear log', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></a>
			</div>
			<?php echo $admin_ui->logger::generate_log_html(); ?> 
		</div>
	</div>

	<div id="setting_entities_section" class="tab-target">
		<?php echo $admin_ui->generate_entities_html(); ?>

		<p><?php _e( 'Detailed', AINSYS_CONNECTOR_TEXTDOMAIN ); ?> <a href="https://github.com/ainsys/ainsys-wp-connector"> <?php _e( ' API integration', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></a> <?php _e( ' documentation.', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></p>
	</div>
</div>

<script>
	jQuery( document ).ready( function ( $ ) {
		$( '#full-uninstall-checkbox' ).on( 'click', function () {
			let val = $( this ).val() == 1 ? 0 : 1
			$( this ).attr( 'value', val )
			$( this ).prop( 'checked', val )
		} );
	} )
</script>
