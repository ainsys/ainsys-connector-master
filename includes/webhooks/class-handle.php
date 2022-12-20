<?php

namespace Ainsys\Connector\Master\Webhooks;

use Ainsys\Connector\Master\Core;
use Ainsys\Connector\Master\Logger;

class Handle {

	protected static string $entity = '';


	/**
	 * Initializes WordPress hooks for component.
	 *
	 * @return void
	 */
	public function init_hooks() {

		add_filter( 'ainsys_webhook_action_handlers', [ $this, 'register_webhook_handler' ], 10, 1 );
	}


	public function handler( $action, $data, $object_id = 0 ) {

		$response = '';

		if ( empty( $action ) ) {
			$response = __( 'Action not registered', AINSYS_CONNECTOR_TEXTDOMAIN );
		}

		switch ( $action ) {
			case 'CREATE':
				$response = $this->create( $data, $action );
				break;
			case 'UPDATE':
				$response = $this->update( $data, $action, $object_id );
				break;
			case 'DELETE':
				$response = $this->delete( $object_id, $data, $action );
				break;
		}

		return $response;
	}


	public function handle_error( $data, $result, $message_error, $entity, $action ): string {

		$message = $message_error . $result->get_error_message();

		Logger::save(
			[
				'object_id'       => 0,
				'entity'          => $entity,
				'request_action'  => $action,
				'request_type'    => 'incoming',
				'request_data'    => serialize( $data ),
				'server_response' => serialize( $message ),
				'error'           => 1,
			]
		);

		Core::send_error_email( $message );

		return $message;
	}


	/**
	 * @param $entity
	 * @param $action
	 * @param $user_id
	 *
	 * @return string
	 */
	public function message_success( $entity, $action, $user_id ): string {

		return sprintf(
			__( '%s has been successfully %s - %s ID:  %s', AINSYS_CONNECTOR_TEXTDOMAIN ),
			ucwords( strtolower( $entity ) ),
			strtolower( $action ),
			$entity,
			$user_id
		);
	}


	public function statuses(): array {

		return [
			'draft',
			'future',
			'pending',
			'private',
			'publish',
			'trash',
		];
	}


	public function replace_string(): array {

		return [
			'CREATE' => 'created',
			'UPDATE' => 'updated',
			'DELETE' => 'deleted',
			'READ'   => 'read',
		];
	}


	/**
	 * @param         $result
	 * @param  array  $data
	 * @param  string $action
	 * @param  string $entity
	 *
	 * @return string
	 */
	public function get_message( $result, array $data, string $entity, string $action ): string {

		if ( is_wp_error( $result ) ) {
			$error = sprintf( __( 'Error: %s is not %s: ', AINSYS_CONNECTOR_TEXTDOMAIN ), $entity, $this->replace_string()[ $action ] );

			$message = $this->handle_error( $data, $result, $error, $entity, $action );
		} else {
			$message = $this->message_success( $entity, $action, $result );
		}

		Logger::save(
			[
				'object_id'       => $result,
				'entity'          => $entity,
				'request_action'  => $action,
				'request_type'    => 'incoming',
				'request_data'    => serialize( $data ),
				'server_response' => serialize( $message ),
			]
		);

		return $message;
	}

}