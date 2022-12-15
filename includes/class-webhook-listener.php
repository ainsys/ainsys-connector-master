<?php

namespace Ainsys\Connector\Master;

/**
 * AINSYS webhook listener.
 *
 * @class          AINSYS webhook listener
 * @version        1.0.0
 * @author         AINSYS
 */
class Webhook_Listener implements Hooked {

	use Is_Singleton;

	/**
	 * @var Logger
	 */
	private Logger $logger;


	public function __construct( Logger $logger ) {

		$this->logger = $logger;
	}


	public function init_hooks() {

		add_action( 'init', [ $this, 'webhook_listener' ] );
	}


	/**
	 * Listens WebHooks using a specific param 'ainsys_webhook'.
	 *
	 */
	public function webhook_listener(): void {

		if ( empty( $_SERVER['QUERY_STRING'] ) ) {
			return;
		}

		parse_str( $_SERVER['QUERY_STRING'], $query_vars );

		if ( ! isset( $query_vars['ainsys_webhook'] ) ) {
			return;
		}

		$entityBody = file_get_contents( 'php://input' );

		// by default, we respond with bad request - if it's right action it will be set below.
		$response_code = 400;
		$response      = false;

		try {
			$request = json_decode( $entityBody, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \Exception $exception ) {
			$response      = $exception->getMessage();
			$response_code = 500;

			$this->logger::save(
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

				$this->logger::save(
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

		wp_send_json(
			[
				'entityType'   => $entityType,
				'request_data' => $data,
				'response'     => $response,
			],
			$response_code
		);

	}


	/**
	 * Generate hook
	 *
	 * @return string
	 */
	public static function get_webhook_url(): string {

		return site_url( '/?ainsys_webhook=' . self::get_request_token(), 'https' );
	}


	public static function get_request_token(): string {

		return sha1( $_SERVER["REMOTE_ADDR"] . $_SERVER["SERVER_NAME"] );
	}

}
