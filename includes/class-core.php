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
	 * @throws \Exception
	 */
	public function curl_exec_func( array $post_fields = [], string $url = '' ) {
		$url = $url ? : (string) $this->settings::get_option( 'ansys_api_key' );

		if ( empty( $url ) ) {
			/// Save curl requests for debug
			$this->settings::set_option( 'debug_log', $this->settings::get_option( 'debug_log' ) . 'cURL Error: No url provided<br>' );

			throw new \RuntimeException( 'No url provided' );
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

		/// Save curl requests for debug
		$logged_string = is_wp_error( $response ) ? $response->get_error_message() : wp_json_encode( $response );
		$this->log( $logged_string );

		if ( is_wp_error( $response ) ) {
			//throw new \Exception( $response->get_error_message(), $response->get_error_code() );
			throw new \Exception( $response->get_error_message() . ' Error code: ' . $response->get_error_code() );
		}

		return $response['body'] ?? '';
	}

	/**
	 * Log any errors.
	 *
	 * @param string $log The log message.
	 */
	public function log( $log ) {
		$this->settings::set_option( 'debug_log', $this->settings::get_option( 'debug_log' ) . $log . '<br>' );
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
