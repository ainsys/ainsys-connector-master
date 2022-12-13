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
	 * @var Logger
	 */
	private $logger;

	/**
	 * @var Settings
	 */
	private $settings;

	public function __construct( Logger $logger, Settings $settings ) {
		$this->logger   = $logger;
		$this->settings = $settings;

	}

	/**
	 * Hooks init to WP.
	 *
	 */
	public function init_hooks() {

	}

	/**
	 * Curl connect and get data.
	 *
	 * @param  array  $post_fields
	 * @param  string $url
	 *
	 * @return string
	 */
	public function curl_exec_func( array $post_fields = [], string $url = '' ): string {
		$url = $url ? : (string) $this->settings::get_option( 'ansys_api_key' );

		if ( empty( $url ) ) {

			$this->logger::save_log_information(
				[
					'object_id'       => 0,
					'entity'          => 'cURL',
					'request_action'  => 'curl_exec_func',
					'request_type'    => 'outgoing',
					'request_data'    => '',
					'server_response' => serialize( 'No url provided' ),
					'error'           => 1,
				]
			);

		}

		$response = wp_remote_post(
			$url,
			array(
				'timeout'     => 30,
				'redirection' => 10,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array( 'content-type' => 'application/json' ),
				'body'        => wp_json_encode( $post_fields, 256 ),
				'cookies'     => array(),
				'sslverify'   => false,
			)
		);

		if ( is_wp_error( $response ) ) {

			$this->logger::save_log_information(
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
		}

		return $response['body'] ?? '';
	}

	/**
	 * Send email in case of AINSYS server errors.
	 *
	 * @param string $message
	 */
	public function send_error_email( $message ) {
		$mail_to = '';
		if ( ! empty( $this->settings::get_backup_email() ) && filter_var( $this->settings::get_backup_email(), FILTER_VALIDATE_EMAIL ) ) {
			$mail_to .= $this->settings::get_backup_email();
		}
		for ( $i = 1; $i < 10; $i++ ) {
			if ( ! empty( $this->settings::get_backup_email( $i ) ) && filter_var( $this->settings::get_backup_email( $i ), FILTER_VALIDATE_EMAIL ) ) {
				$mail_to .= ',' . $this->settings::get_backup_email( $i );
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
