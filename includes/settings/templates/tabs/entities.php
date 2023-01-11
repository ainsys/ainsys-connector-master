<?php
/**
 * Settings Entities Controlling Tab
 *
 * @package ainsys
 *
 * @global                                            $args
 * @global  Ainsys\Connector\Master\Settings\Admin_UI $admin_ui
 */

use Ainsys\Connector\Master\Settings\Admin_UI_Entities_Controlling;
use Ainsys\Connector\Master\Settings\Settings;

$admin_ui    = $args['admin_ui'];
$active      = $args['active'];
$columns     = Admin_UI_Entities_Controlling::columns_entities_controlling();
$option_name = Settings::get_option_name( 'check_controlling_entity' );
$option      = Settings::get_option( 'check_controlling_entity' );

?>

<div id="setting-section-entities" class="tab-target">
	<table class="ainsys-table ainsys-table--controlling-entities">
		<thead>
			<?php foreach ( $columns as $column_id => $column_name ) : ?>

				<th class="ainsys-table--header ainsys-table--header--<?php echo esc_attr( $column_id ); ?>">
					<span class="ainsys-table--header--title"><?php echo esc_html( $column_name ); ?></span>
				</th>
			<?php endforeach; ?>
		</thead>

		<?php foreach ( Settings::get_entities() as $entity_id => $entity_label ) : ?>

			<tr class="ainsys-table-table__row ainsys-table__row--id-<?php echo esc_attr( $entity_id ); ?> ">
				<?php foreach ( $columns as $column_id => $column_name ) : ?>
					<td class="ainsys-table-table__cell ainsys-table-table__cell-<?php echo esc_attr( $column_id ); ?>"
					    data-title="<?php echo esc_attr( $column_name ); ?>">

						<?php

						$option_item        = $option[ $entity_id ]['general'][ $column_id ] ?? 1;
						$option_name_column = sprintf( '-%s', $column_id );
						$option_name_item   = sprintf( '-%s%s', $entity_id, $option_name_column );
						$option_on_off      = $option[ $entity_id ]['general']['on_off'] ?? 1;

						switch ( $column_id ):
							case 'arrow' :
								?>
								<div class="ainsys-table-table__cell--<?php echo esc_attr( $column_id ); ?>--inside">

									<button type="button"
									        class="ainsys-arrow"
									        data-entity-name="<?php echo esc_attr( $entity_id ); ?>">

										<svg fill="none" viewBox="0 0 24 24">
											<g clip-path="url(#a)">
												<path fill="#AB47BC" d="M7.41 8.59 12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41Z"/>
											</g>
											<defs>
												<clipPath id="a">
													<path fill="#fff" d="M24 0v24H0V0z"/>
												</clipPath>
											</defs>
										</svg>
									</button>
								</div>
								<?php
								break;
							case 'entity' :

								?>
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
								<?php

								break;
							case 'on_off' :
								?>
								<div class="ainsys-table-table__cell--<?php echo esc_attr( $column_id ); ?>--inside">
									<div class="ainsys-form-group group-checkbox--toggle group-checkbox--toggle-<?php echo esc_attr( $option_name_item ); ?>">
										<label class="toggle toggle-<?php echo esc_attr( $option_name_column ); ?> toggle-<?php echo esc_attr( $option_name_item ); ?>">
											<input type="checkbox"
											       class="toggle-checkbox toggle-checkbox-<?php echo esc_attr( $option_name_column ); ?> toggle-checkbox-<?php echo esc_attr(
												       $option_name_item
											       ); ?>"
											       id="checkbox-<?php echo esc_attr( $option_name_item ); ?>"
											       name="<?php echo esc_attr( $option_name ); ?>[<?php echo esc_attr( $entity_id ); ?>][general][<?php echo esc_attr(
												       $column_id
											       ); ?>]"
											       value="<?php echo esc_attr( $option_on_off ); ?>"
											       data-toggle-checkbox-entity-id="<?php echo esc_attr( $entity_id ); ?>"
											       data-toggle-checkbox-column-id="<?php echo esc_attr( $column_id ); ?>"
												<?php checked( 1, esc_html( $option_on_off ) ); ?> >
											<span class="toggle-switch"></span>
											<span class="toggle-label"><?php echo esc_attr( $column_name ); ?></span>
										</label>
									</div>
								</div>
								<?php
								break;
							case 'create' :
							case 'read' :
							case 'update' :
							case 'delete' :
								?>
								<div class="ainsys-table-table__cell--<?php echo esc_attr( $column_id ); ?>--inside">
									<div class="ainsys-form-group group-checkbox--toggle group-checkbox--toggle-<?php echo esc_attr( $option_name_item ); ?>">
										<label class="toggle toggle-<?php echo esc_attr( $option_name_column ); ?> toggle-<?php echo esc_attr( $option_name_item ); ?>">
											<input type="checkbox"
											       class="toggle-checkbox toggle-checkbox-<?php echo esc_attr( $option_name_column ); ?> toggle-checkbox-<?php echo esc_attr( $option_name_item ); ?>"
											       id="checkbox-<?php echo esc_attr( $option_name_item ); ?>"
											       name="<?php echo esc_attr( $option_name ); ?>[<?php echo esc_attr( $entity_id ); ?>][general][<?php echo esc_attr(
												       $column_id ); ?>]"
											       value="<?php echo esc_attr( $option_item ); ?>"
											       data-toggle-checkbox-entity-id="<?php echo esc_attr( $entity_id ); ?>"
											       data-toggle-checkbox-column-id="<?php echo esc_attr( $column_id ); ?>"
												<?php checked( 1, esc_attr( $option_item ) ); ?>
												<?php disabled( 0, esc_attr( $option_on_off ) ); ?>
											>
											<span class="toggle-switch"></span>
											<span class="toggle-label"><?php echo esc_attr( $column_name ); ?></span>
										</label>
									</div>
								</div>
								<?php

								break;
							case 'last_exchange' :
								?>
								<div class="ainsys-table-table__cell--<?php echo esc_attr( $column_id ); ?>--inside">
									<?php if ( empty( $option[ $entity_id ]['general']['time'] ) ): ?>
										<?php esc_html_e( 'No data', AINSYS_CONNECTOR_TEXTDOMAIN ) ?>
									<?php else: ?>
										<?php echo esc_html( $option[ $entity_id ]['general']['time'] ); ?>
									<?php endif; ?>
								</div>
								<?php
								break;
							case 'log' :
								?>
								<button type="button"
								        class="btn btn-primary"
								        data-entity-name="<?php echo esc_attr( $entity_id ); ?>">
									<?php echo esc_html( __( 'Log', AINSYS_CONNECTOR_TEXTDOMAIN ) ); ?>
								</button>
								<?php
								break;

						endswitch;
						?>

					</td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
		<tbody>
		</tbody>

	</table>
</div>
