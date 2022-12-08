<?php
/**
 * Settings Logs Tab
 *
 * @package ainsys
 *
 * @global $args
 */
$admin_ui = $args['admin_ui'];
$active = $args['active'];

?>
<div id="setting-section-log" class="tab-target">
	<?php
	$start    = $admin_ui->settings::get_option( 'do_log_transactions' ) ? ' disabled' : '';
	$stop     = $admin_ui->settings::get_option( 'do_log_transactions' ) ? '' : ' disabled';
	$since    = $admin_ui->settings::get_option( 'log_transactions_since' ) ?? '';
	$time     = $admin_ui->settings::get_option( 'log_until_certain_time' ) ?? 0;
	$selected = $admin_ui->settings::get_option( 'log_select_value' ) ?? -1;
	?>
	<div class="ainsys-log-block">
		<div class="ainsys-log-status">
			<div class="ainsys-log-time"><?php echo $time; ?></div>
			<span class="ainsys-log-status-title">Log Status: </span>
			<?php
			$log_status_ok_style = '';
			$log_status_no_style = '';
			if ( $admin_ui->settings::get_option( 'do_log_transactions' ) ) {
				$log_status_ok_style = ' style="display: inline;"';
				$log_status_no_style = ' style="display: none;"';
			} else {
				$log_status_ok_style = ' style="display: none;"';
				$log_status_no_style = ' style="display: inline;"';
			}
			?>
			<span class="ainsys-log-status-ok"<?php echo $log_status_ok_style; ?>><i class="fa fa-check-circle-o" aria-hidden="true"></i> <?php _e( 'Working since', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?> <span class="ainsys-log-since"><?php echo esc_html( $since ); ?></span></span>
			<span class="ainsys-log-status-no"<?php echo $log_status_no_style; ?>><i class="fa fa-times-circle-o" aria-hidden="true"></i> <?php _e( 'Not Working', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></span>
			<span class="ainsys-status-loading"><?php _e( 'Loading...', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></span>
		</div>
		<div class="ainsys-log-controls">
			<a id="start_loging" class="btn btn-primary ainsys-log-control<?php echo esc_attr( $start ); ?>"><?php _e( 'Start loging', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></a>

			<select id="start_loging_timeinterval" class="<?php echo esc_attr( $start ); ?>" <?php echo esc_attr( $start ); ?> name="loging_timeinterval">
				<option value="1"<?php if ( 1 == $selected ) { echo ' selected="selected"';} ?>><?php _e( '1 hour', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></option>
				<option value="5"<?php if ( 5 == $selected ) { echo ' selected="selected"';} ?>><?php _e( '5 hours', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></option>
				<option value="12"<?php if ( 12 == $selected ) { echo ' selected="selected"';} ?>><?php _e( '12 hours', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></option>
				<option value="24"<?php if ( 24 == $selected ) { echo ' selected="selected"';} ?>><?php _e( '24 hours', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></option>
				<option value="-1"<?php if ( -1 == $selected ) { echo ' selected="selected"';} ?>><?php _e( 'unlimited', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></option>
			</select>

			<a id="stop_loging" class="btn btn-primary ainsys-log-control<?php echo esc_attr( $stop ); ?>"><?php _e( 'Stop loging', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></a>

			<a id="reload_log" class="btn btn-primary"><?php _e( 'Reload', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></a>

			<a id="clear_log" class="btn btn-primary"><?php _e( 'Clear log', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></a>
		</div>
		<?php echo $admin_ui->logger::generate_log_html(); ?>
	</div>
</div>