<?php

namespace Ainsys\Connector\Master;

use Ainsys\Connector\Master\Settings\Settings;

class Logger implements Hooked {

	public static $do_log_transactions = false;

	public static $clear_full_uninstall;

	/**
	 * @var array|string[]
	 */
	protected static array $settings_tables;

	protected static string $log_table_name;


	public function __construct( Settings $settings ) {

		self::$do_log_transactions  = $settings::get_option( 'do_log_transactions' );
		self::$clear_full_uninstall = $settings::get_option( 'full_uninstall' );

		self::$settings_tables = $settings::get_settings_tables();
		self::$log_table_name = self::$settings_tables['logs'];
	}


	public function init_hooks() {}


	/**
	 * Save each update transactions to log
	 *
	 *
	 * @param $args
	 *
	 * @return bool|int|\mysqli_result|resource|null
	 */
	public static function save_log_information( $args ) {

		global $wpdb;

		if ( ! self::$do_log_transactions ) {
			return false;
		}

		$defaults = [
			'object_id'       => 0,
			'entity'          => '',
			'request_action'  => '',
			'request_type'    => '',
			'request_data'    => '',
			'server_response' => '',
			'error'           => 0,
		];

		$args = wp_parse_args( $args, $defaults );

		return $wpdb->insert(
			$wpdb->prefix . self::$log_table_name,
			$args
		);
	}


	/**
	 * Render json as HTML.
	 *
	 * @param         $json
	 * @param  string $result
	 *
	 * @return string
	 */
	public static function ainsys_render_json( $json, string $result = '' ): string {

		foreach ( $json as $key => $val ) {

			if ( ! is_object( $val ) && ! is_array( $val ) ) {
				$result .= sprintf( '<div class="ainsys-json-inner">%s : %s</div>', $key, $val );
			} else {
				$result .= sprintf( '{<div class="ainsys-json-outer"> %s : %s</div>}<br>', $key, self::ainsys_render_json( $val ) );
			}
		}

		return $result;
	}


	/**
	 * Generate server data transactions HTML.
	 *
	 * @param  string $where
	 *
	 * @return string
	 */
	public static function generate_log_html( string $where = '' ): string {

		global $wpdb;

		$log_html        = '<div id="connection_log"><table class="ainsys-table display"style="width:100%">';
		$log_html_body   = '';
		$log_html_header = '';

		$query  = sprintf( "SELECT * FROM %s %s", $wpdb->prefix . self::$log_table_name, $where );
		$output = $wpdb->get_results( $query, ARRAY_A );

		if ( empty( $output ) ) {
			return '<div class="empty_tab"><h3>' . __( 'No transactions to display', AINSYS_CONNECTOR_TEXTDOMAIN ) . '</h3></div>'; // phpcs:ignore
		}

		foreach ( $output as $item ) {
			$class_error   = $item['error'] ? 'class="error"' : '';
			$log_html_body .= '<tr ' . $class_error . '>';
			$header_full   = empty( $log_html_header );

			foreach ( $item as $name => $value ) {

				$log_html_header .= $header_full ? sprintf( '<th class="%s">%s</th>', $name, strtoupper( str_replace( '_', ' ', $name ) ) ) : '';

				$log_html_body .= '<td class="' . $name . '">';

				if ( $name === 'request_data' || $name === 'server_response' ) {

					$value = maybe_unserialize( $value );

					if ( empty( $value ) ) {

            $log_html_body .= __( 'EMPTY', AINSYS_CONNECTOR_TEXTDOMAIN );

					} else {
						$log_html_body .= '<div class="ainsys-response-short">' . mb_substr( serialize( $value ), 0, 40 ) . ' ... </div>';

						if ( is_array( $value ) ) {
							$value = wp_json_encode( $value );
						}

						try {
							$value_out = json_decode( $value, true, 512, JSON_THROW_ON_ERROR );
						} catch ( \JsonException $exception ) {
							$value_out = $value;
						}

						$log_html_body .= '<div class="ainsys-response-full">';

						if ( is_string( $value_out ) ) {
							$log_html_body .= $value_out;
						} else {
							$log_html_body .= self::ainsys_render_json( $value_out );
						}

						$log_html_body .= '</div>';
					}
				} else {
					$log_html_body .= is_array( $value ) ? serialize( $value ) : $value;
				}

				$log_html_body .= '</td>';
			}

			$log_html_body .= '</tr>';

		}

		$log_html .= '<thead><tr>' . $log_html_header . '</tr></thead><tbody>' . $log_html_body . '</tbody></table> </div>';

		return $log_html;
	}


	/**
	 * Truncate log table.
	 *
	 */
	public static function truncate_log_table(): void {

		global $wpdb;

		$wpdb->query( sprintf( "TRUNCATE TABLE %s", $wpdb->prefix . self::$log_table_name ) );

	}


	/**
	 * Install tables
	 */
	public function activate(): void {

		ob_start();
		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $this->get_schema() );

		ob_get_clean();
	}


	public function deactivate(): void {

		if ( self::$clear_full_uninstall ) {
			$this->uninstall();
		}
	}


	public function uninstall(): void {

		global $wpdb;

		$wpdb->query( sprintf( "DROP TABLE IF EXISTS %s", $wpdb->prefix . self::$log_table_name ) );

	}


	/**
	 * Get Table schema.
	 *
	 * @return string
	 */
	private function get_schema(): string {

		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		/*
		 * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
		 * As of WordPress 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
		 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
		 *
		 * This may cause duplicate index notices in logs due to https://core.trac.wordpress.org/ticket/34870 but dropping
		 * indexes first causes too much load on some servers/larger DB.
		 */

		$table_log = $wpdb->prefix . self::$log_table_name;

		return "CREATE TABLE $table_log (
                `log_id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `creation_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `object_id` bigint NOT NULL,
                `entity` varchar(100) NOT NULL,
                `request_action` varchar(100) NOT NULL,
                `request_type` varchar(100) NOT NULL,
                `request_data` text DEFAULT NULL,
                `server_response` text DEFAULT NULL,
                `error` smallint NOT NULL,
                PRIMARY KEY  (log_id),
                KEY object_id (object_id)
            ) $collate;";

	}

}
