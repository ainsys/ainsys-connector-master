<?php

namespace Ainsys\Connector\Master\Webhooks;

use Ainsys\Connector\Master\Core;
use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Logger;
use Ainsys\Connector\Master\Webhook_Handler;

class Handle_User implements Hooked, Webhook_Handler {

	/**
	 * @var \Ainsys\Connector\Master\Logger
	 */
	protected Logger $logger;

	/**
	 * @var Core
	 */
	private Core $core;


	public function __construct( Core $core, Logger $logger ) {

		$this->core   = $core;
		$this->logger = $logger;
	}


	/**
	 * Initializes WordPress hooks for component.
	 *
	 * @return void
	 */
	public function init_hooks() {

		add_filter( 'ainsys_webhook_action_handlers', [ $this, 'register_webhook_handler' ], 10, 1 );
	}


	public function register_webhook_handler( $handlers = [] ) {

		$handlers['user'] = [ $this, 'handler' ];

		return $handlers;
	}


	public function handler( $action, $data, $object_id = 0 ) {

		$data = (array) $data;

		$response = __( 'Action not registered', AINSYS_CONNECTOR_TEXTDOMAIN );

		switch ( $action ) {
			case 'CREATE':
				$response = $this->create( $data );
				break;
			case 'UPDATE':
				$response = $this->update( $data, $object_id );
				break;
			case 'DELETE':
				$response = wp_delete_user( $object_id );
				break;
		}

		return $response;
	}


	protected function update( $data ): string {

		$success = __( 'The user has been successfully updated: user ID ', AINSYS_CONNECTOR_TEXTDOMAIN );
		$error   = __( 'An error has occurred, perhaps such a user does not exist', AINSYS_CONNECTOR_TEXTDOMAIN );

		$result = wp_update_user( $data );

		if ( is_wp_error( $result ) ) {
			$message = $error . $result->get_error_message();

			$this->logger::save(
				[
					'object_id'       => 0,
					'entity'          => 'user',
					'request_action'  => 'CREATE',
					'request_type'    => 'incoming',
					'request_data'    => serialize( $data ),
					'server_response' => serialize( $message ),
					'error'           => 1,
				]
			);

			$this->core->send_error_email( $message );

			return $message;
		}

		$message = $success . $result;

		$this->logger::save(
			[
				'object_id'       => $result,
				'entity'          => 'user',
				'request_action'  => 'UPDATE',
				'request_type'    => 'incoming',
				'request_data'    => serialize( $data ),
				'server_response' => serialize( $message ),
			]
		);

		return $message;
	}


	/**
	 * @param  array $data
	 *
	 * @return string
	 */
	protected function create( array $data ): string {

		$success = __( 'The user has been successfully created: user ID ', AINSYS_CONNECTOR_TEXTDOMAIN );
		$error   = __( 'An error occurred when creating a user: ', AINSYS_CONNECTOR_TEXTDOMAIN );

		$data['user_pass'] = $data['user_pass'] ?? wp_generate_password( 15, true, true );

		$user_id = wp_insert_user( $data );

		if ( is_wp_error( $user_id ) ) {
			$message = $error . $user_id->get_error_message();

			$this->logger::save(
				[
					'object_id'       => 0,
					'entity'          => 'user',
					'request_action'  => 'CREATE',
					'request_type'    => 'incoming',
					'request_data'    => serialize( $data ),
					'server_response' => serialize( $message ),
					'error'           => 1,
				]
			);

			$this->core->send_error_email( $message );

			return $message;
		}

		$message = $success . $user_id;

		$this->logger::save(
			[
				'object_id'       => $user_id,
				'entity'          => 'user',
				'request_action'  => 'CREATE',
				'request_type'    => 'incoming',
				'request_data'    => serialize( $data ),
				'server_response' => serialize( $message ),
			]
		);

		return $message;
	}

}