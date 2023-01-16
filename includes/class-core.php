<?php

namespace Ainsys\Connector\Master;


use Ainsys\Connector\Master\Settings\Settings;


/**
 * AINSYS connector core.
 *
 * @class          AINSYS connector core
 * @version        1.0.0
 * @author         AINSYS
 */
class Core implements Hooked {

	/**
	 * Hooks init to WP.
	 *
	 */
	public function init_hooks() { }


	/**
	 * Curl connect and get data.
	 *
	 * @param  array  $post_fields
	 * @param  string $url
	 *
	 * @return string
	 */
	public static function curl_exec_func( array $post_fields = [], string $url = '' ): string {

		$url = $url ? : (string) Settings::get_option( 'ansys_api_key' );

		if ( empty( $url ) ) {

			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => 'check_url',
					'request_action'  => 'curl_exec_func',
					'request_type'    => 'outgoing',
					'request_data'    => '',
					'server_response' => serialize( 'Error: No url provided' ),
					'error'           => 1,
				]
			);

			return 'Error: The URL is missing. Specify the required URL in the plugin settings on the General tab' ;
		}

		$response = wp_remote_post(
			$url,
			[
				'timeout'     => 30,
				'redirection' => 10,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => [ 'content-type' => 'application/json' ],
				'body'        => wp_json_encode( $post_fields, 256 ),
				'cookies'     => [],
				'sslverify'   => false,
			]
		);

		if ( is_wp_error( $response ) ) {

			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => 'cURL',
					'request_action'  => 'curl_exec_func',
					'request_type'    => 'outgoing',
					'request_data'    => '',
					'server_response' => serialize( sprintf( '%s Error code: %s', $response->get_error_message(), $response->get_error_code() ) ),
					'error'           => 1,
				]
			);

			return $response->get_error_message();
		}

		return $response['body'] ? : '';
	}


	/**
	 * Send email in case of AINSYS server errors.
	 *
	 * @param  string $message
	 */
	public static function send_error_email( $message ) {

		$mail_to = '';
		if ( ! empty( Settings::get_backup_email() ) && filter_var( Settings::get_backup_email(), FILTER_VALIDATE_EMAIL ) ) {
			$mail_to .= Settings::get_backup_email();
		}
		for ( $i = 1; $i < 10; $i ++ ) {
			if ( ! empty( Settings::get_backup_email( $i ) ) && filter_var( Settings::get_backup_email( $i ), FILTER_VALIDATE_EMAIL ) ) {
				$mail_to .= ',' . Settings::get_backup_email( $i );
			}
		}

		$urlparts = parse_url( home_url() );
		$domain   = $urlparts['host'];

		$headers = 'From: AINSYS <noreply@' . $domain . '>' . "\r\n";

		if ( ! empty( $mail_to ) ) {
			mail( $mail_to, 'Error message', $message, $headers );
		}
	}

}
