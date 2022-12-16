<?php
/**
 * Settings Logs Tab
 *
 * @package ainsys
 *
 * @global                                            $args
 * @global  Ainsys\Connector\Master\Settings\Admin_UI $admin_ui
 */

use Ainsys\Connector\Master\Settings\Admin_UI_Logs;
use Ainsys\Connector\Master\Settings\Settings;

$admin_ui = $args['admin_ui'];
$active   = $args['active'];

$time_select = Admin_UI_Logs::select_time();

if ( Settings::get_option( 'do_log_transactions' ) ) {
	$log_status_ok_style = ' style="display: inline;"';
	$log_status_no_style = ' style="display: none;"';
} else {
	$log_status_ok_style = ' style="display: none;"';
	$log_status_no_style = ' style="display: inline;"';
}

$start    = Settings::get_option( 'do_log_transactions' ) ? ' disabled' : '';
$stop     = Settings::get_option( 'do_log_transactions' ) ? '' : ' disabled';
$since    = Settings::get_option( 'log_transactions_since' ) ?? '';
$time     = Settings::get_option( 'log_until_certain_time' ) ?? 0;
$selected = Settings::get_option( 'log_select_value' ) ?? - 1;

?>
<div id="setting-section-log" class="tab-target">
	<div class="ainsys-log-block">

		<div class="ainsys-log-status">
			<div class="ainsys-log-time"><?php echo $time; ?></div>
			<span class="ainsys-log-status-title">Log Status: </span>
			<span class="ainsys-log-status-ok"<?php echo $log_status_ok_style; ?>><i class="fa fa-check-circle-o" aria-hidden="true"></i> <?php _e(
					'Working since', AINSYS_CONNECTOR_TEXTDOMAIN
				); ?> <span class="ainsys-log-since"><?php echo esc_html( $since ); ?></span></span>
			<span class="ainsys-log-status-no"<?php echo $log_status_no_style; ?>><i class="fa fa-times-circle-o" aria-hidden="true"></i> <?php _e(
					'Not Working', AINSYS_CONNECTOR_TEXTDOMAIN
				); ?></span>
			<span class="ainsys-status-loading"><?php _e( 'Loading...', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></span>
		</div>

		<div class="ainsys-log-controls">
			<button type="button"
			        id="start_loging"
			        class="btn btn-primary ainsys-log-control<?php echo esc_attr( $start ); ?>"><?php _e( 'Start log', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></button>

			<select id="start_loging_timeinterval" class="<?php echo esc_attr( $start ); ?>" <?php echo esc_attr( $start ); ?> name="loging_timeinterval">
				<?php foreach ( $time_select as $key => $val ): ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected, $key ); ?>><?php echo esc_html( $val ); ?></option>
				<?php endforeach; ?>
			</select>

			<button type="button"
			        id="stop_loging"
			        class="btn btn-primary ainsys-log-control<?php echo esc_attr( $stop ); ?>"><?php _e( 'Stop log', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></button>

			<button type="button"
			        id="reload_log"
			        class="btn btn-primary"><?php _e( 'Reload log', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></button>

			<button type="button"
			        id="clear_log" class="btn btn-primary"><?php _e( 'Clear log', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></button>
		</div>

		<div id="connection_log" class="ainsys-log-table">
			<?php echo Admin_UI_Logs::generate_log_html(); ?>
		</div>

	</div>
</div>