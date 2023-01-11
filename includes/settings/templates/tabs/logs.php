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
	$log_status_ok_style = 'display: inline;';
	$log_status_no_style = 'display: none;';
} else {
	$log_status_ok_style = 'display: none;';
	$log_status_no_style = 'display: inline;';
}

$start    = Settings::get_option( 'do_log_transactions' ) ? ' disabled' : '';
$stop     = Settings::get_option( 'do_log_transactions' ) ? '' : ' disabled';
$since    = Settings::get_option( 'log_transactions_since' ) ?? '';
$time     = Settings::get_option( 'log_until_certain_time' ) ?? 0;
$selected = empty( Settings::get_option( 'log_select_value' ) ) ? 1 : Settings::get_option( 'log_select_value' );

?>
<div id="setting-section-log" class="tab-target">
	<div class="ainsys-log-block">

		<div class="ainsys-log-status">
			<div class="ainsys-log-time"><?php esc_html_e( $time ); ?></div>
			<span class="ainsys-log-status-title"><?php esc_html_e( 'Log Status: ', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></span>
			<span class="ainsys-log-status-ok" style="<?php esc_attr_e( $log_status_ok_style ); ?>">
				<?php esc_html_e( 'Working since', AINSYS_CONNECTOR_TEXTDOMAIN ); ?> <span class="ainsys-log-since"><?php esc_html_e( $since ); ?></span>
			</span>
			<span class="ainsys-log-status-no" style="<?php esc_attr_e( $log_status_no_style ); ?>">
				<?php esc_html_e( 'Not Working', AINSYS_CONNECTOR_TEXTDOMAIN ); ?>
			</span>
			<span class="ainsys-status-loading"><?php esc_html_e( 'Loading...', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></span>
		</div>

		<div class="ainsys-log-controls">
			<button type="button"
			        id="start_loging"
			        class="btn btn-primary ainsys-log-control<?php esc_attr_e( $start ); ?>"><?php _e( 'Start log', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></button>

			<select id="start_loging_timeinterval"
			        class="<?php esc_attr_e( $start ); ?>" <?php esc_attr_e( $start ); ?>
			        name="<?php esc_attr_e( Settings::get_option_name( 'log_select_value' ) ); ?>loging_timeinterval">
				<?php foreach ( $time_select as $key => $val ): ?>
					<option value="<?php esc_attr_e( $key ); ?>" <?php selected( $selected, $key ); ?>><?php esc_html_e( $val ); ?></option>
				<?php endforeach; ?>
			</select>

			<div class="ainsys-form-input">
				<label for="<?php esc_attr_e( Settings::get_option_name( 'do_log_transactions' ) ); ?>" class="ainsys-form-label">
					<input id="<?php esc_attr_e( Settings::get_option_name( 'do_log_transactions' ) ); ?>"
					       type="hidden"
					       name="<?php esc_attr_e( Settings::get_option_name( 'do_log_transactions' ) ); ?>"
					       value="<?php esc_attr_e( ! empty( Settings::get_option( 'do_log_transactions' ) ? : 1 ) ); ?>"/>
				</label>
			</div>

			<button type="button"
			        id="stop_loging"
			        class="btn btn-primary ainsys-log-control<?php esc_attr_e( $stop ); ?>"><?php esc_html_e( 'Stop log', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></button>

			<button type="button"
			        id="reload_log"
			        class="btn btn-primary"><?php esc_html_e( 'Reload log', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></button>

			<button type="button"
			        id="clear_log" class="btn btn-primary"><?php esc_html_e( 'Clear log', AINSYS_CONNECTOR_TEXTDOMAIN ); ?></button>
		</div>

		<div id="connection_log" class="ainsys-log-table">
			<?php echo wp_kses_post( Admin_UI_Logs::generate_log_html() ); ?>
		</div>

	</div>
</div>