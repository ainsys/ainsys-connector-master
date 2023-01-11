<?php

namespace Ainsys\Connector\Master;

use Ainsys\Connector\Master\Settings\Settings;

class Logger implements Hooked {


	public function init_hooks() { }

	protected static function table_name() {

		return Settings::get_settings_tables()['logs'];
	}

	/**
	 * Save each update transactions to log
	 *
	 *
	 * @param $args
	 *
	 * @return bool|int|\mysqli_result|resource|null
	 */
	public static function save( $args ) {

		global $wpdb;

		if ( ! Settings::get_option( 'do_log_transactions' ) ) {
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
			$wpdb->prefix . self::table_name(),
			$args,
			[ '%d', '%s', '%s', '%s', '%s', '%s', '%d' ]
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
	public static function render_json( $json, string $result = '' ): string {

		foreach ( $json as $key => $val ) {

			if ( ! is_object( $val ) && ! is_array( $val ) ) {
				$result .= sprintf( '<div class="ainsys-json-inner">%s : %s</div>', $key, $val );
			} else {
				$result .= sprintf( '{<div class="ainsys-json-outer"> %s : %s</div>}<br>', $key, self::render_json( $val ) );
			}
		}

		return $result;
	}


	/**
	 * @param $response
	 *
	 * @return string
	 */
	public static function convert_response( $response ): string {

		if ( is_array( $response ) ) {
			$response = wp_json_encode( $response );
		}

		try {
			$value_out = json_decode( $response, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \JsonException $exception ) {
			$value_out = $response;
		}

		if ( is_string( $value_out ) ) {
			$full_response = $value_out;
		} else {
			try {
				$full_response = json_encode( $value_out, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			} catch ( \JsonException $exception ) {
				$full_response = $exception->getMessage();
			}
		}

		return $full_response;
	}

}
