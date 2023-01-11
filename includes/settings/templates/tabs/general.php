<?php
/**
 * Settings General Tab
 *
 * @package ainsys
 *
 * @global                                            $args
 * @global  Ainsys\Connector\Master\Settings\Admin_UI $admin_ui
 */

use Ainsys\Connector\Master\Settings\Admin_UI_General;
use Ainsys\Connector\Master\Settings\Settings;

$admin_ui = $args['admin_ui'];
$active   = $args['active'];
$settings = new Admin_UI_General( $admin_ui );

$status_system = $settings->get_statuses_system();
$status_addons = $settings->get_statuses_addons();

?>

<div id="setting-section-general" class="tab-target">
	<div class="ainsys-settings-blocks">
		<div class="ainsys-settings-block--primary">
			<div class="ainsys-settings-block ainsys-settings-block--connection">
				<h2><?php esc_html_e( 'Connection Settings', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></h2>


				<?php settings_fields( Settings::get_option_name( 'group' ) ); ?>
				<div class="ainsys-form-group">
					<label for="ansys-api-key" class="ainsys-form-label">
						<?php _e( 'AINSYS handshake url for the connector. You can find it in your ', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
						<a href="https://app.ainsys.com/dashboard" target="_blank">
							<?php esc_html_e( 'dashboard', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
						</a>.
					</label>
					<div class="ainsys-form-input">
						<input id="ansys-api-key"
						       type="text"
						       size="50"
						       required
						       name="<?php echo esc_attr( Settings::get_option_name( 'ansys_api_key' ) ); ?>"
						       placeholder="XXXXXXXXXXXXXXXXXXXXX"
						       value="<?php echo esc_attr( Settings::get_option( 'ansys_api_key' ) ); ?>"/>
					</div>
				</div>
				<div class="ainsys-form-group">
					<label for="hook-url" class="ainsys-form-label">
						<?php esc_html_e( 'Server hook_url', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
					</label>
					<div class="ainsys-form-input">
						<input id="hook-url"
						       type="text"
						       size="50"
						       name="<?php echo esc_attr( Settings::get_option_name( 'webhook_url' ) ); ?>"
						       value="<?php echo esc_attr( Settings::get_option( 'webhook_url' ) ); ?>"
						       disabled/>
					</div>
				</div>

				<div class="ainsys-form-group ainsys-email ainsys-email-main">
					<label for="backup-email" class="ainsys-form-label">
						<?php esc_html_e( 'E-mail for error reports', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
					</label>
					<div class="ainsys-form-input">
						<input id="backup-email"
						       type="text"
						       name="<?php echo esc_attr( Settings::get_option_name( 'backup_email' ) ); ?>"
						       placeholder="backup@email.com"
						       value="<?php echo esc_attr( Settings::get_backup_email() ); ?>"/>
						<div class="ainsys-email-btn ainsys-plus" data-target="1">+</div>
					</div>
				</div>
				<?php
				for ( $i = 1; $i < 10; $i ++ ) {
					?>
					<div class="ainsys-form-group ainsys-email<?php echo ! empty( Settings::get_backup_email( $i ) ) ? ' ainsys-email-show' : ''; ?>"
					     data-block-id="<?php echo esc_attr( $i ); ?>">
						<label for="backup-email-<?php echo esc_attr( $i ); ?>" class="ainsys-form-label">
							<?php _e( 'E-mail for error reports', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
							<span class="ainsys-form-label-note"><?php esc_html_e( 'Additional email error reports', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></span>
						</label>
						<div class="ainsys-form-input">
							<input id="backup-email-<?php echo esc_attr( $i ); ?>"
							       type="text"
							       name="<?php echo esc_attr( Settings::get_option_name( 'backup_email_' . $i ) ); ?>"
							       placeholder="backup@email.com"
							       value="<?php echo esc_attr( Settings::get_backup_email( $i ) ); ?>"/>
							<div class="ainsys-email-btn ainsys-plus" data-target="<?php echo esc_attr( $i ) + 1; ?>">+</div>
							<div class="ainsys-email-btn ainsys-minus">–</div>
						</div>
					</div>
				<?php } ?>


				<div class="ainsys-form-group">
					<label for="connector-id" class="ainsys-form-label">
						<?php esc_html_e( 'Connector Id', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
					</label>
					<div class="ainsys-form-input">
						<input id="connector-id"
						       type="text"
						       size="50"
						       name="<?php echo esc_attr( Settings::get_option_name( 'connector_id' ) ); ?>"
						       value="<?php echo esc_attr( Settings::get_option( 'connector_id' ) ); ?>"/>
					</div>
				</div>

				<div class="submit">
					<input type="submit" class="btn btn-primary" value="<?php esc_html_e( 'Save', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>"/>
				</div>

			</div>

			<div class="ainsys-settings-block ainsys-settings-block--disconnection">
				<?php if ( Settings::get_option( 'ansys_api_key' ) ) : ?>
					<button type="button"
					        id="remove_ainsys_integration"
					        class="btn btn-secondary"><?php esc_html_e( 'Disconnect integration', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></button>
				<?php endif; ?>
				<div class="ainsys-form-group ainsys-form-group-checkbox">
					<div class="ainsys-form-input">
						<input id="full-uninstall-checkbox"
						       type="checkbox"
						       name="<?php echo esc_attr( Settings::get_option_name( 'full_uninstall' ) ); ?>"
						       value="<?php echo esc_attr( Settings::get_option( 'full_uninstall' ) ); ?>"
							<?php checked( 1, esc_attr( Settings::get_option( 'full_uninstall' ) ) ); ?> />
					</div>
					<label for="full-uninstall-checkbox" class="ainsys-form-label">
						<?php esc_html_e( 'Purge all plugin data during deactivation to reset connector settings ', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
						<span class="ainsys-form-label-note"><?php esc_html_e(
								'NB: if you delete the plugin from WordPress admin panel it will clear data regardless of this checkbox', AINSYS_CONNECTOR_TEXTDOMAIN
							); ?></span>
					</label>
				</div>
			</div>
		</div>
		<div class="ainsys-settings-block--sidebar">

			<div class="ainsys-settings-block ainsys-settings-block--status ">
				<?php if ( ! empty( $status_system ) ): ?>
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
									<svg class="ainsys-icon ainsys-icon--error" fill="none" viewBox="0 0 24 24"><g fill="#D5031E" clip-path="url(#a)"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/><path
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
				<?php endif; ?>

				<?php if ( ! empty( $status_addons ) ): ?>
					<div class="ainsys-settings-block--status--addons ainsys-overline">
						<h2><?php esc_html_e( 'Add-ons and plugin status', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></h2>

						<ul class="ainsys-status-items ainsys-li-overline">
							<?php foreach ( $status_addons as $status_key => $status_item ) : ?>
								<li class="ainsys-status">
									<span class="ainsys-status--title"><?php echo esc_html( $status_item['title'] ); ?></span>

									<?php if ( empty( $status_item['install'] ) ): ?>
										<span class="ainsys-status--error  ainsys-status--state">
										<svg class="ainsys-icon ainsys-icon--error" fill="none" viewBox="0 0 24 24"><g fill="#D5031E" clip-path="url(#a)"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/><path
													stroke="#D5031E"
													stroke-width=".5"
													d="m17 8-1-1-4 4-4-4-1 1 4 4-4 4 1 1 4-4 4 4 1-1-4-4 4-4Z"/></g><defs><clipPath id="a"><path fill="#fff"
										                                                                                                         d="M0 0h24v24H0z"/></clipPath></defs></svg>
										<?php

										printf(
											'%s <a href="#">%s</a>',
											esc_html__( 'Not installed', AINSYS_CONNECTOR_TEXTDOMAIN ),
											__( 'Install', AINSYS_CONNECTOR_TEXTDOMAIN )
										);
										?>

									<?php elseif ( empty( $status_item['active'] ) ): ?>
										</span>
										<span class="ainsys-status--error  ainsys-status--state">
									<svg class="ainsys-icon ainsys-icon--error" fill="none" viewBox="0 0 24 24"><g fill="#D5031E" clip-path="url(#a)"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/><path
												stroke="#D5031E"
												stroke-width=".5"
												d="m17 8-1-1-4 4-4-4-1 1 4 4-4 4 1 1 4-4 4 4 1-1-4-4 4-4Z"/></g><defs><clipPath id="a"><path fill="#fff"
									                                                                                                         d="M0 0h24v24H0z"/></clipPath></defs></svg>
								<?php
								printf(
									'%s <a href="%s" class="thickbox">%s</a>',
									esc_html__( 'Not activated', AINSYS_CONNECTOR_TEXTDOMAIN ),
									'plugins.php',
									__( 'Activate', AINSYS_CONNECTOR_TEXTDOMAIN )
								);
								?>
								</span>
									<?php else : ?>
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
								<?php esc_html_e( 'Active', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
								</span>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>

						</ul>
					</div>
				<?php endif; ?>

			</div>


			<div class="ainsys-settings-block ainsys-settings-block--connect-status">

				<h2><?php _e( 'Test connection', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></h2>
				<ul class="ainsys-status-items ainsys-underline">
					<li class="ainsys-status ainsys-status--check-integration">
						<span class="ainsys-status--title"><?php esc_html_e( 'Connection', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></span>
						<?php if ( Settings::get_option( 'ansys_api_key' ) ) : ?>
							<span class="ainsys-status--ok ainsys-status--state">
									<svg fill="none" viewBox="0 0 24 24"><g clip-path="url(#a)"><path fill="#37B34A"
									                                                                  d="M16.59 7.58 10 14.17l-3.59-3.58L5 12l5 5 8-8-1.41-1.42ZM12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/></g><defs><clipPath
												id="a"><path fill="#fff" d="M0 0h24v24H0z"/></clipPath></defs></svg>
								<?php esc_html_e( 'Working', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
								</span>
						<?php else : ?>
							<span class="ainsys-status--error  ainsys-status--state">
									<svg class="ainsys-icon ainsys-icon--error" fill="none" viewBox="0 0 24 24"><g fill="#D5031E" clip-path="url(#a)"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/><path
												stroke="#D5031E"
												stroke-width=".5"
												d="m17 8-1-1-4 4-4-4-1 1 4 4-4 4 1 1 4-4 4 4 1-1-4-4 4-4Z"/></g><defs><clipPath id="a"><path fill="#fff"
									                                                                                                         d="M0 0h24v24H0z"/></clipPath></defs></svg>
								<?php esc_html_e( 'Not working', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
								</span>
						<?php endif; ?>
					</li>
				</ul>
				<div class="ainsys-settings-block--connect-status--last-operation">
					<?php
					$last_operation = empty( Settings::get_option( 'check_connection' )['time'] )
						? esc_html__( 'No data', AINSYS_CONNECTOR_TEXTDOMAIN )
						: Settings::get_option( 'check_connection' )['time'];

					?>
					<a href="#" class="">Last operation: <span><?php echo esc_html( $last_operation ); ?></span></a>
				</div>
				<div class="ainsys-check-integration">
					<button type="button"
					        id="check_ainsys_integration"
					        class="btn btn-primary"><?php esc_html_e( 'Check integration', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></button>
				</div>

			</div>

		</div>


	</div>

</div>
