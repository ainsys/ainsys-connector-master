<?php

/**
 * Settings General Tab
 *
 * @package ainsys
 *
 * @global $args
 */
$admin_ui = $args['admin_ui'];
$active   = $args['active'];

try {
	$status = $admin_ui->is_ainsys_integration_active( 'check' );
} catch ( \Exception $e ) {
	echo esc_html( $e->getMessage() );
}

$status_system = $admin_ui->get_statuses_system();
$status_addons = $admin_ui->get_statuses_addons();
do_action( 'qm/info', $status_addons );

?>

<div id="setting-section-general" class="tab-target">
	<div class="ainsys-settings-blocks">
		<div class="ainsys-settings-block ainsys-settings-block--connection">
			<h2><?php _e( 'Connection Settings', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></h2>

			<form method="post" action="options.php">
				<?php settings_fields( $admin_ui->settings::get_option_name( 'group' ) ); ?>
				<div class="ainsys-form-group">
					<label for="ansys-api-key" class="ainsys-form-label">
						<?php _e( 'AINSYS handshake url for the connector. You can find it in your ', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?>
						<a href="https://app.ainsys.com/en/settings/workspaces" target="_blank">
							<?php _e( 'dashboard', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?>
						</a>.
					</label>
					<div class="ainsys-form-input">
						<input id="ansys-api-key"
						       type="text"
						       size="50"
						       name="<?php echo esc_html( $admin_ui->settings::get_option_name( 'ansys_api_key' ) ); ?>"
						       placeholder="XXXXXXXXXXXXXXXXXXXXX"
						       value="<?php echo esc_html( $admin_ui->settings::get_option( 'ansys_api_key' ) ); ?>"/>
					</div>
				</div>
				<div class="ainsys-form-group">
					<label for="hook-url" class="ainsys-form-label">
						<?php _e( 'Server hook_url', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?>
					</label>
					<div class="ainsys-form-input">
						<input id="hook-url"
						       type="text"
						       size="50"
						       name="<?php echo esc_attr( $admin_ui->settings::get_option_name( 'hook_url' ) ); ?>"
						       value="<?php echo esc_attr( $admin_ui->settings::get_option( 'hook_url' ) ); ?>"
						       disabled/>
					</div>
				</div>

				<div class="ainsys-form-group ainsys-email ainsys-email-main">
					<label for="backup-email" class="ainsys-form-label">
						<?php _e( 'E-mail for error reports', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?>
					</label>
					<div class="ainsys-form-input">
						<input id="backup-email"
						       type="text"
						       name="<?php echo esc_attr( $admin_ui->settings::get_option_name( 'backup_email' ) ); ?>"
						       placeholder="backup@email.com"
						       value="<?php echo esc_attr( $admin_ui->settings::get_backup_email() ); ?>"/>
						<div class="ainsys-email-btn ainsys-plus" data-target="1">+</div>
					</div>
				</div>
				<?php
				for ( $i = 1; $i < 10; $i ++ ) {
					?>
					<div class="ainsys-form-group ainsys-email<?php echo ! empty( $admin_ui->settings::get_backup_email( $i ) ) ? ' ainsys-email-show' : ''; ?>"
					     data-block-id="<?php echo esc_attr( $i ); ?>">
						<label for="backup-email-<?php echo esc_attr( $i ); ?>" class="ainsys-form-label">
							<?php _e( 'E-mail for error reports', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
							<span class="ainsys-form-label-note"><?php _e( 'Additional email error reports', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></span>
						</label>
						<div class="ainsys-form-input">
							<input id="backup-email-<?php echo esc_attr( $i ); ?>"
							       type="text"
							       name="<?php echo esc_attr( $admin_ui->settings::get_option_name( 'backup_email_' . $i ) ); ?>"
							       placeholder="backup@email.com"
							       value="<?php echo esc_attr( $admin_ui->settings::get_backup_email( $i ) ); ?>"/>
							<div class="ainsys-email-btn ainsys-plus" data-target="<?php echo $i + 1; ?>">+</div>
							<div class="ainsys-email-btn ainsys-minus">–</div>
						</div>
					</div>
				<?php } ?>


				<div class="ainsys-form-group">
					<label for="connector-id" class="ainsys-form-label">
						<?php _e( 'Connector Id', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?>
					</label>
					<div class="ainsys-form-input">
						<input id="connector-id"
						       type="text"
						       size="50"
						       name="<?php echo esc_attr( $admin_ui->settings::get_option_name( 'connector_id' ) ); ?>"
						       value="<?php echo esc_attr( $admin_ui->settings::get_option( 'connector_id' ) ); ?>"/>
					</div>
				</div>

				<div class="submit">
					<input type="submit" class="btn btn-primary" value="<?php // phpcs:ignore
					if ( ! empty( $status ) && 'success' === $status['status'] ) {
						_e( 'Save', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore
					} else {
						_e( 'Connect', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore
					}
					// phpcs:ignore ?>"/>
					<?php if ( ! empty( $status ) && 'success' === $status['status'] ) { ?>
						<a id="remove_ainsys_integration" class="btn btn-secondary"><?php _e( 'Disconect integration', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></a>
					<?php } ?>
				</div>
				<div class="ainsys-form-group ainsys-form-group-checkbox">
					<div class="ainsys-form-input">
						<input id="full-uninstall-checkbox"
						       type="checkbox"
						       name="<?php echo esc_attr( $admin_ui->settings::get_option_name( 'full_uninstall' ) ); ?>"
						       value="<?php echo esc_attr( $admin_ui->settings::get_option( 'full_uninstall' ) ); ?>" <?php checked(
							1, esc_html( $admin_ui->settings::get_option( 'full_uninstall' ) ), true
						); ?> />
					</div>
					<label for="full-uninstall-checkbox" class="ainsys-form-label">
						<?php _e( 'Purge all stored data during deactivation ', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?>
						<span class="ainsys-form-label-note"><?php _e(
								'NB: if you delete the plugin from WordPress admin panel it will clear data regardless of this checkbox', AINSYS_CONNECTOR_TEXTDOMAIN
							); // phpcs:ignore ?></span>
					</label>
				</div>
			</form>

		</div>
		<div class="ainsys-settings-block--sidebar">
			<div class="ainsys-settings-block ainsys-settings-block--status ">

				<div class="ainsys-settings-block--status--system ainsys-underline">
					<h2><?php _e( 'Wordpress settings', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></h2>

					<ul class="ainsys-status-items ainsys-li-overline">
						<?php foreach ( $status_system as $status_key => $status_item ) : ?>
							<li class="ainsys-status">
								<span class="ainsys-status--title"><?php echo esc_html( $status_item['title'] ); ?></span>
								<?php if ( $status_item['active'] ) : ?>
									<span class="ainsys-status--ok ainsys-status--state">
									<svg fill="none" viewBox="0 0 24 24"><g clip-path="url(#a)"><path fill="#37B34A"
									                                                                  d="M16.59 7.58 10 14.17l-3.59-3.58L5 12l5 5 8-8-1.41-1.42ZM12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/></g><defs><clipPath
												id="a"><path fill="#fff" d="M0 0h24v24H0z"/></clipPath></defs></svg>
								<?php echo esc_html( $status_item['label_success'] ); ?>
								</span>
								<?php else : ?>
									<span class="ainsys-status--error  ainsys-status--state">
									<svg fill="none" viewBox="0 0 24 24"><g fill="#D5031E" clip-path="url(#a)"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/><path
												stroke="#D5031E"
												stroke-width=".5"
												d="m17 8-1-1-4 4-4-4-1 1 4 4-4 4 1 1 4-4 4 4 1-1-4-4 4-4Z"/></g><defs><clipPath id="a"><path fill="#fff"
									                                                                                                         d="M0 0h24v24H0z"/></clipPath></defs></svg>
								<?php echo esc_html( $status_item['label_error'] ); ?>
								</span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>


					</ul>
				</div>

				<div class="ainsys-settings-block--status--addons ainsys-overline">
					<h2><?php _e( 'Add-ons and plugin status', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></h2>

					<ul class="ainsys-status-items ainsys-li-overline">
						<?php foreach ( $status_addons as $status_key => $status_item ) : ?>
							<li class="ainsys-status">
								<span class="ainsys-status--title"><?php echo esc_html( $status_item['title'] ); ?></span>

								<?php if ( ! $status_item['install'] ): ?>
								<span class="ainsys-status--error  ainsys-status--state">
									<svg fill="none" viewBox="0 0 24 24"><g fill="#D5031E" clip-path="url(#a)"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/><path
												stroke="#D5031E"
												stroke-width=".5"
												d="m17 8-1-1-4 4-4-4-1 1 4 4-4 4 1 1 4-4 4 4 1-1-4-4 4-4Z"/></g><defs><clipPath id="a">
												<path fill="#fff"
												      d="M0 0h24v24H0z"/></clipPath></defs>
									</svg>
								<?php

								printf(
									'%s <a href="#">%s</a>',
									esc_html( __( 'Not installed', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
									__( 'Install', AINSYS_CONNECTOR_TEXTDOMAIN )
								);

								elseif ( ! $status_item['active'] ):

								?>
							<span class="ainsys-status--error  ainsys-status--state">
									<svg fill="none" viewBox="0 0 24 24"><g fill="#D5031E" clip-path="url(#a)"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/><path
												stroke="#D5031E"
												stroke-width=".5"
												d="m17 8-1-1-4 4-4-4-1 1 4 4-4 4 1 1 4-4 4 4 1-1-4-4 4-4Z"/></g><defs><clipPath id="a">
												<path fill="#fff"
												      d="M0 0h24v24H0z"/></clipPath></defs>
									</svg>
								<?php
								printf(
									'%s <a href="%s" class="thickbox">%s</a>',
									esc_html( __( 'Not activated', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
									'plugins.php',
									__( 'Activate', AINSYS_CONNECTOR_TEXTDOMAIN )
								);

								else :

									?>
									<span class="ainsys-status--ok ainsys-status--state">
									<svg fill="none" viewBox="0 0 24 24">
										<g clip-path="url(#a)">
											<path fill="#37B34A"
											      d="M16.59 7.58 10 14.17l-3.59-3.58L5 12l5 5 8-8-1.41-1.42ZM12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/>
										</g>
										<defs>
											<clipPath
												id="a"><path fill="#fff" d="M0 0h24v24H0z"/></clipPath>
										</defs>
									</svg>
								<?php echo esc_html( __( 'Active', AINSYS_CONNECTOR_TEXTDOMAIN ) ); ?>
								</span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>

					</ul>
				</div>


			</div>

			<div class="ainsys-settings-block ainsys-settings-block--connect-status">

				<h2><?php _e( 'Test connection', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></h2>

				<span class="ainsys-status-title"><?php _e( 'Conection', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></span>
				<?php
				if ( ! empty( $status ) && 'success' === $status['status'] ) :
					?>
					<span class="ainsys-status-ok">
								<i class="fa fa-check-circle-o" aria-hidden="true"></i> <?php _e( 'Working', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore
						?>
							</span>
				<?php
				else :
					?>
					<span class="ainsys-status-error">
								<i class="fa fa-times-circle-o" aria-hidden="true"></i> <?php _e( 'No AINSYS integration', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore
						?>
							</span>
				<?php
				endif;
				?>
			</div>

		</div>


	</div>

</div>
