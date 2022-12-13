<?php

/**
 * Settings General Tab
 *
 * @package ainsys
 *
 * @global                                            $args
 * @global  Ainsys\Connector\Master\Settings\Admin_UI $admin_ui
 */

use Ainsys\Connector\Master\Settings\Admin_UI_Checking_Entities;

$admin_ui = $args['admin_ui'];
$active   = $args['active'];
$settings = new Admin_UI_Checking_Entities( $admin_ui );

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

				<?php foreach ( $settings->entities_list() as $entity_id => $entity_label ) : ?>

					<tr class="ainsys-table-table__row ainsys-table__row--id-<?php echo esc_attr( $entity_id ); ?> ">
						<?php foreach ( $settings->columns_checking_entities() as $column_id => $column_name ) : ?>
							<td class="ainsys-table-table__cell ainsys-table-table__cell-<?php echo esc_attr( $column_id ); ?>"
							    data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php if ( 'entity' === $column_id ) : ?>
								<div class="ainsys-table-table__cell--entity">
									<span><?php echo esc_html( $entity_label ); ?></span>
									<a href="#setting-section-entities">
										<svg fill="none" viewBox="0 0 20 20"><g clip-path="url(#a)"><path fill="#AB47BC" d="M15.95 10.78a5.88 5.88 0 0 0 0-1.56l1.68-1.32a.4.4 0 0 0 .1-.5l-1.6-2.78c-.1-.18-.3-.24-.49-.18l-1.99.8a5.88 5.88 0 0 0-1.35-.78L12 2.34a.4.4 0 0 0-.4-.34H8.4a.4.4 0 0 0-.4.34l-.3 2.12c-.48.2-.93.47-1.34.78l-2-.8a.4.4 0 0 0-.49.18L2.28 7.4c-.1.18-.06.4.1.51l1.7 1.32a4.89 4.89 0 0 0-.02 1.56l-1.7 1.32a.4.4 0 0 0-.1.5l1.6 2.78c.1.18.31.24.5.18l1.99-.8c.42.31.86.58 1.35.78l.3 2.12c.04.2.2.34.4.34h3.2c.2 0 .37-.14.4-.34l.3-2.12c.48-.2.93-.46 1.34-.78l2 .8c.18.06.38 0 .48-.19l1.6-2.76c.1-.18.06-.4-.1-.51l-1.67-1.32ZM10 13a3 3 0 0 1-3-3 3 3 0 0 1 3-3 3 3 0 0 1 3 3 3 3 0 0 1-3 3Z"/></g><defs><clipPath id="a"><path fill="#fff" d="M0 0h20v20H0z"/></clipPath></defs></svg>
									</a>
								</div>


								<?php elseif ( 'outgoing' === $column_id ) : ?>
									<div class="ainsys-response-short"></div>
									<div class="ainsys-response-full"></div>
								<?php elseif ( 'server_response' === $column_id ) : ?>
									<div class="ainsys-response-short"></div>
									<div class="ainsys-response-full"></div>

								<?php elseif ( 'time' === $column_id ) : ?>

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