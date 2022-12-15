<?php

/**
 * Settings General Tab
 *
 * @package ainsys
 *
 * @global                                            $args
 * @global  Ainsys\Connector\Master\Settings\Admin_UI $admin_ui
 */

use Ainsys\Connector\Master\Logger;
use Ainsys\Connector\Master\Settings\Admin_UI_Entities_Checking;
use Ainsys\Connector\Master\Settings\Settings;

$admin_ui     = $args['admin_ui'];
$active       = $args['active'];
$settings     = new Admin_UI_Entities_Checking();
$check_entity = Settings::get_option( 'check_connection_entity' );

?>

<div id="setting-section-test" class="tab-target">
	<div class="ainsys-test-block">
		<div id="connection_test">
			<table class="ainsys-table ainsys-table--checking-entities">
				<thead>
					<?php foreach ( $settings->columns_checking_entities() as $column_id => $column_name ) : ?>

						<th class="ainsys-table--header ainsys-table--header--<?php echo esc_attr( $column_id ); ?>">
							<span class="ainsys-table--header--title"><?php echo esc_html( $column_name ); ?></span>
						</th>
					<?php endforeach; ?>
				</thead>

				<?php foreach ( Settings::get_entities() as $entity_id => $entity_label ) : ?>

					<tr class="ainsys-table-table__row ainsys-table__row--id-<?php echo esc_attr( $entity_id ); ?> ">
						<?php foreach ( $settings->columns_checking_entities() as $column_id => $column_name ) : ?>
							<td class="ainsys-table-table__cell ainsys-table-table__cell-<?php echo esc_attr( $column_id ); ?>"
							    data-title="<?php echo esc_attr( $column_name ); ?>">

								<?php if ( 'entity' === $column_id ) : ?>

									<div class="ainsys-table-table__cell--<?php echo esc_attr( $column_id ); ?>--inside">
										<span><?php echo esc_html( $entity_label ); ?></span>
										<a href="#setting-section-entities">
											<svg fill="none" viewBox="0 0 20 20">
												<g clip-path="url(#a)">
													<path fill="#AB47BC"
													      d="M15.95 10.78a5.88 5.88 0 0 0 0-1.56l1.68-1.32a.4.4 0 0 0 .1-.5l-1.6-2.78c-.1-.18-.3-.24-.49-.18l-1.99.8a5.88 5.88 0 0 0-1.35-.78L12 2.34a.4.4 0 0 0-.4-.34H8.4a.4.4 0 0 0-.4.34l-.3 2.12c-.48.2-.93.47-1.34.78l-2-.8a.4.4 0 0 0-.49.18L2.28 7.4c-.1.18-.06.4.1.51l1.7 1.32a4.89 4.89 0 0 0-.02 1.56l-1.7 1.32a.4.4 0 0 0-.1.5l1.6 2.78c.1.18.31.24.5.18l1.99-.8c.42.31.86.58 1.35.78l.3 2.12c.04.2.2.34.4.34h3.2c.2 0 .37-.14.4-.34l.3-2.12c.48-.2.93-.46 1.34-.78l2 .8c.18.06.38 0 .48-.19l1.6-2.76c.1-.18.06-.4-.1-.51l-1.67-1.32ZM10 13a3 3 0 0 1-3-3 3 3 0 0 1 3-3 3 3 0 0 1 3 3 3 3 0 0 1-3 3Z"/>
												</g>
												<defs>
													<clipPath id="a">
														<path fill="#fff" d="M0 0h20v20H0z"/>
													</clipPath>
												</defs>
											</svg>
										</a>
									</div>

								<?php elseif ( 'outgoing' === $column_id ) : ?>

									<div class="ainsys-table-table__cell--<?php echo esc_attr( $column_id ); ?>--inside">
										<?php if ( empty( $check_entity[ $entity_id ]['request'] ) ): ?>

											<div class="ainsys-response-short"><?php esc_html_e( 'No data', AINSYS_CONNECTOR_TEXTDOMAIN ) ?></div>
											<div class="ainsys-response-full"></div>
										<?php else: ?>

											<div class="ainsys-response-short">
												<?php echo mb_substr( serialize( $check_entity[ $entity_id ]['request'] ), 0, 40 ) . ' ... ' ?>
											</div>
											<div class="ainsys-response-full">

												<?php echo Logger::ainsys_render_json( $check_entity[ $entity_id ]['request'] ) ?>
											</div>
										<?php endif; ?>

									</div>

								<?php elseif ( 'server_response' === $column_id ) : ?>

									<div class="ainsys-table-table__cell--<?php echo esc_attr( $column_id ); ?>--inside">
										<?php if ( empty( $check_entity[ $entity_id ]['response'] ) ): ?>

											<div class="ainsys-response-short"><?php esc_html_e( 'No data', AINSYS_CONNECTOR_TEXTDOMAIN ) ?></div>
											<div class="ainsys-response-full"></div>
										<?php else: ?>
											<div class="ainsys-response-short">
												<?php echo mb_substr( serialize( $check_entity[ $entity_id ]['response'] ), 0, 40 ) . ' ... ' ?>
											</div>
											<div class="ainsys-response-full">
												<?php echo $check_entity[ $entity_id ]['response']; ?>
											</div>
										<?php endif; ?>
									</div>

								<?php elseif ( 'time' === $column_id ) : ?>

									<div class="ainsys-table-table__cell--<?php echo esc_attr( $column_id ); ?>--inside">
										<?php if ( empty( $check_entity[ $entity_id ]['time'] ) ): ?>
											<?php esc_html_e( 'No data', AINSYS_CONNECTOR_TEXTDOMAIN ) ?>
										<?php else: ?>
											<?php echo esc_html( $check_entity[ $entity_id ]['time'] ); ?>
										<?php endif; ?>
									</div>

								<?php elseif ( 'check' === $column_id ) : ?>

									<button type="button"
									        class="btn btn-primary ainsys-check"
									        data-entity-name="<?php echo esc_attr( $entity_id ); ?>">
										<?php echo esc_html( __( 'Check', AINSYS_CONNECTOR_TEXTDOMAIN ) ); ?>
									</button>

								<?php elseif ( 'status' === $column_id ) : ?>
									<span class="ainsys-success"></span>
									<span class="ainsys-failure"></span>
									<?php

									?>
									<div class="ainsys-table-table__cell--<?php echo esc_attr( $column_id ); ?>--inside">
										<?php if ( empty( $check_entity[ $entity_id ]['status'] ) ) : ?>
											<span class="ainsys-status--error  ainsys-status--state">
										<svg fill="none" viewBox="0 0 24 24"><g fill="#D5031E" clip-path="url(#a)"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/><path
													stroke="#D5031E"
													stroke-width=".5"
													d="m17 8-1-1-4 4-4-4-1 1 4 4-4 4 1 1 4-4 4 4 1-1-4-4 4-4Z"/></g><defs><clipPath id="a"><path fill="#fff"
										                                                                                                         d="M0 0h24v24H0z"/></clipPath></defs></svg>
										<?php echo esc_html( __( 'No connection', AINSYS_CONNECTOR_TEXTDOMAIN ) ); ?>
									</span>
										<?php else: ?>
											<span class="ainsys-status--ok ainsys-status--state"><svg fill="none" viewBox="0 0 24 24"><g clip-path="url(#a)"><path fill="#37B34A"
											                                                                                                                       d="M16.59 7.58 10 14.17l-3.59-3.58L5 12l5 5 8-8-1.41-1.42ZM12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/></g><defs><clipPath
															id="a"><path fill="#fff" d="M0 0h24v24H0z"/></clipPath></defs></svg><?php echo esc_html(
													__( 'Connection', AINSYS_CONNECTOR_TEXTDOMAIN )
												); ?></span>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
				<tbody>
				</tbody>

			</table>
		</div>
	</div>
</div>