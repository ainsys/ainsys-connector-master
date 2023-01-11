<?php

namespace Ainsys\Connector\Master\Settings;

use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Logger;

class Admin_UI_Logs implements Hooked {

	/**
	 * Init plugin hooks.
	 */
	public function init_hooks() {

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'wp_ajax_reload_log_html', [ $this, 'reload_log_html' ] );
		add_action( 'wp_ajax_toggle_logging', [ $this, 'toggle_logging' ] );
		add_action( 'wp_ajax_clear_log', [ $this, 'clear_log' ] );

	}


	public static function select_time(): array {

		return apply_filters(
			'ainsys_select_time_logs',
			[
				'60'    => __( '1 min', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'900'   => __( '15 min', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'1800'  => __( '30 min', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'3600'  => __( '1 hour', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'10800' => __( '3 hours', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'21600' => __( '6 hours', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'43200' => __( '12 hours', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'86400' => __( '24 hours', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'-1'    => __( 'Unlimited', AINSYS_CONNECTOR_TEXTDOMAIN ),
			]
		);
	}


	/**
	 * Regenerates log HTML (for ajax).
	 *
	 */
	public function reload_log_html(): void {

		if ( ! isset( $_POST['action'] ) ) {
			wp_die( __( 'Action is missing', AINSYS_CONNECTOR_TEXTDOMAIN ) );
		}

		echo self::generate_log_html();

		wp_die();
	}


	/**
	 * Clears log DB table (for ajax).
	 *
	 */
	public function clear_log(): void {

		if ( ! isset( $_POST['action'] ) ) {
			wp_die( __( 'Action is missing', AINSYS_CONNECTOR_TEXTDOMAIN ) );
		}

		Settings::truncate_tables( 'ainsys_log' );
		echo self::generate_log_html();

		wp_die();
	}


	/**
	 * Toggles logging on/off. Set up time till log is saved (for ajax).
	 *
	 */
	public function toggle_logging(): void {

		if ( ! isset( $_POST['command'] ) ) {
			wp_die( __( 'Command is missing', AINSYS_CONNECTOR_TEXTDOMAIN ) );
		}

		$command  = sanitize_text_field( $_POST['command'] );
		$time     = sanitize_text_field( $_POST['time'] );
		$start_at = sanitize_text_field( $_POST['startat'] );

		$logging_time = 0;

		if ( isset( $time ) ) {

			$current_time = time();
			$time         = (float) ( $time ?? 0 );
			$end_time     = $time;

			if ( $time > 0 ) {
				$end_time = $current_time + $time;
			}

			Settings::set_option( 'log_until_certain_time', $end_time );
			Settings::set_option( 'log_select_value', $time );

			$logging_time = $end_time;
		}

		$logging_since = '';

		if ( 'start_loging' === $command ) {
			Settings::set_option( 'do_log_transactions', 1 );
			Settings::set_option( 'log_transactions_since', $start_at );
			$logging_since = Settings::get_option( 'log_transactions_since' );
		} else {
			Settings::set_option( 'do_log_transactions', 0 );
			Settings::set_option( 'log_transactions_since', '' );
			Settings::set_option( 'log_select_value', $time );
		}

		wp_send_json( [
			'logging_time'  => $logging_time,
			'logging_since' => $logging_since,
		] );

	}


	/**
	 * Generate server data transactions HTML.
	 *
	 * @param  string $where
	 *
	 * @return string
	 */
	public static function generate_log_html( string $where = '' ): string {


		$log_html        = '<table class="ainsys-table display" style="width:100%">';
		$log_html_body   = '';
		$log_html_header = '';

		$output = self::get_logs_query( $where );

		if ( empty( $output ) ) {
			return sprintf( '<div class="empty_tab"><h3>%s</h3></div>', __( 'No transactions to display', AINSYS_CONNECTOR_TEXTDOMAIN ) );
		}

		foreach ( $output as $item ) {
			$class_error   = $item['error'] ? 'class="error"' : '';
			$log_html_body .= sprintf( '<tr %s>', $class_error );
			$header_full   = empty( $log_html_header );

			foreach ( $item as $name => $value ) {

				$log_html_header .= $header_full ? sprintf( '<th class="%s">%s</th>', $name, strtoupper( str_replace( '_', ' ', $name ) ) ) : '';

				$log_html_body .= sprintf( '<td class="%s">', $name );

				if ( $name === 'request_data' || $name === 'server_response' ) {

					$value = maybe_unserialize( $value );

					if ( empty( $value ) ) {
						$log_html_body .= __( 'EMPTY', AINSYS_CONNECTOR_TEXTDOMAIN );
					} else {
						$log_html_body .= sprintf( '<div class="ainsys-response-short">%s...</div>', mb_substr( Logger::convert_response( $value ), 0, 40 ) );
						$log_html_body .= sprintf( '<div class="ainsys-response-full"><pre>%s</pre></div>', Logger::convert_response( $value ) );
					}

				} else {
					$log_html_body .= is_array( $value ) ? serialize( $value ) : $value;
				}

				$log_html_body .= '</td>';
			}

			$log_html_body .= '</tr>';

		}

		$log_html .= sprintf( '<thead><tr>%s</tr></thead><tbody>%s</tbody></table>', $log_html_header, $log_html_body );

		return apply_filters( 'ainsys_log_html_table', $log_html, $output );
	}


	/**
	 * @param  string $where
	 *
	 * @return array
	 */
	private static function get_logs_query( string $where ): array {

		global $wpdb;

		$query = sprintf( "SELECT * FROM %s %s", $wpdb->prefix . Settings::get_settings_tables()['logs'], $where );

		return $wpdb->get_results( $query, ARRAY_A );
	}

}