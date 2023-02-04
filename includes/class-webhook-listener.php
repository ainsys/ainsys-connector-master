<?php

namespace Ainsys\Connector\Master;

use Ainsys\Connector\Master\Settings\Settings;
use WP_REST_Request;
use WP_REST_Server;

/**
 * AINSYS webhook listener.
 *
 * @class          AINSYS webhook listener
 * @version        1.0.0
 * @author         AINSYS
 */
class Webhook_Listener implements Hooked {

	use Is_Singleton;

	public function init_hooks() {

		//add_action( 'init', [ $this, 'webhook_listener' ] );

		add_action( 'rest_api_init', [ $this, 'rest_route_webhook_listener' ] );
	}


	public function rest_route_webhook_listener(): void {

		register_rest_route(
			'ainsys/v1',
			'/webhook/(?P<token>[a-zA-Z0-9_-]+)', [
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'rest_route_webhook_callback' ],
					'permission_callback' => [ $this, 'rest_route_webhook_permission_callback' ],

				],
			]
		);

	}


	public function rest_route_webhook_permission_callback( WP_REST_Request $request ): bool {
		error_log( print_r( $request->get_param( 'token' ), 1 ) );
		error_log( print_r( self::get_request_token(), 1 ) );
		error_log( print_r( Settings::get_option( 'token' ), 1 ) );
		return true;//$request->get_param( 'token' ) === Settings::get_option( 'token' );
	}


	public function rest_route_webhook_callback( WP_REST_Request $request ): void {

		if ( ! empty( $_GET['ainsys_webhook'] ) && 'development' === wp_get_environment_type() ) {

			$options = [
				'ssl' => [
					'verify_peer'      => false,
					'verify_peer_name' => false,
				],
			];

			$json_file = ABSPATH . 'testings-development.json';

			$entityBody = file_exists( $json_file ) ? file_get_contents( $json_file, false, stream_context_create( $options ) ) : '';

		} else {

			$entityBody = $request->get_body();

		}

		$response_code = 400;
		$response      = '';

		try {
			$request = json_decode( $entityBody, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \Exception $exception ) {
			$response      = $exception->getMessage();
			$response_code = 500;

			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => 'Webhook_Listener',
					'request_action'  => '',
					'request_type'    => 'parse entityBody',
					'request_data'    => serialize( $entityBody ),
					'server_response' => serialize( $response ),
					'error'           => 1,
				]
			);

		}

		$object_id = $request['entity']['id'] ?? 0;
		$data      = $request['payload'] ?? [];

		$entityAction = $request['action'];
		$entityType   = $request['entity']['name'];

		if ( $entityAction === 'CREATE' || $entityAction === 'DELETE' || $entityAction === 'UPDATE' ) {
			$response_code = 200;
		}

		$action_handlers = apply_filters( 'ainsys_webhook_action_handlers', [] );

		$handler = $action_handlers[ $entityType ];

		if ( is_callable( $handler ) ) {
			try {
				$response = $handler( $entityAction, $data, $object_id );
			} catch ( \Exception $exception ) {
				$response      = $exception->getMessage();
				$response_code = 500;

				Logger::save(
					[
						'object_id'       => 0,
						'entity'          => 'Webhook_Listener',
						'request_action'  => '',
						'request_type'    => 'response handler',
						'request_data'    => serialize( $entityBody ),
						'server_response' => serialize( $response ),
						'error'           => 1,
					]
				);

			}
		} else {
			$response_code = 404;
		}

		$payload = array_merge( $data, $response );

		if ( 'development' !== wp_get_environment_type() ) {
			wp_send_json( $payload, $response_code );
		}
	}


	/**
	 * Listens WebHooks using a specific param 'ainsys_webhook'.
	 *
	 */

	public function webhook_listener(): void {

		if ( ! empty( $_GET['ainsys_webhook'] ) && 'development' === wp_get_environment_type() ) {

			$options = [
				'ssl' => [
					'verify_peer'      => false,
					'verify_peer_name' => false,
				],
			];

			$json_file = ABSPATH . 'testings-development.json';

			$entityBody = file_exists( $json_file ) ? file_get_contents( $json_file, false, stream_context_create( $options ) ) : '';

		} else {

			$query_string = sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) );

			if ( empty( $query_string ) ) {
				return;
			}

			wp_parse_str( $query_string, $query_vars );

			if ( ! isset( $query_vars['ainsys_webhook'] ) ) {
				return;
			}

			$entityBody = file_get_contents( 'php://input' );
		}

		// by default, we respond with bad request - if it's right action it will be set below.
		$response_code = 400;
		$response      = false;

		try {
			$request = json_decode( $entityBody, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \Exception $exception ) {
			$response      = $exception->getMessage();
			$response_code = 500;

			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => 'Webhook_Listener',
					'request_action'  => '',
					'request_type'    => 'parse entityBody',
					'request_data'    => serialize( $entityBody ),
					'server_response' => serialize( $response ),
					'error'           => 1,
				]
			);

		}

		$object_id = $request['entity']['id'] ?? 0;
		$data      = $request['payload'] ?? [];

		$entityAction = $request['action'];
		$entityType   = $request['entity']['name'];

		if ( $entityAction === 'CREATE' || $entityAction === 'DELETE' || $entityAction === 'UPDATE' ) {
			$response_code = 200;
		}

		$action_handlers = apply_filters( 'ainsys_webhook_action_handlers', [] );

		$handler = $action_handlers[ $entityType ];

		if ( is_callable( $handler ) ) {
			try {
				$response = $handler( $entityAction, $data, $object_id );
			} catch ( \Exception $exception ) {
				$response      = $exception->getMessage();
				$response_code = 500;

				Logger::save(
					[
						'object_id'       => 0,
						'entity'          => 'Webhook_Listener',
						'request_action'  => '',
						'request_type'    => 'response handler',
						'request_data'    => serialize( $entityBody ),
						'server_response' => serialize( $response ),
						'error'           => 1,
					]
				);

			}
		} else {
			$response_code = 404;
		}

		if ( 'development' !== wp_get_environment_type() ) {
			wp_send_json(
				[
					'entityType'   => $entityType,
					'request_data' => $data,
					'response'     => $response,
				],
				$response_code
			);
		}
	}


	/**
	 * Generate hook
	 *
	 * @return string
	 */
	public static function get_webhook_url(): string {

		return site_url( '/?ainsys_webhook=' . self::get_request_token(), 'https' );
	}


	public static function get_rest_webhook_url(): string {

		return rest_url( 'ainsys/v1/webhook/' . self::get_request_token() );
	}


	public static function get_request_token(): string {

		return sha1(
			sprintf(
				'%s%s',
				sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ),
				sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) )
			)
		);
	}

}
