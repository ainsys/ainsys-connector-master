<?php

namespace Ainsys\Connector\Master\Webhooks;

use Ainsys\Connector\Master\Conditions;
use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Logger;
use Ainsys\Connector\Master\Webhook_Handler;

class Handle_Comment extends Handle implements Hooked, Webhook_Handler {

	protected static string $entity = 'comment';


	/**
	 * Initializes WordPress hooks for component.
	 *
	 * @return void
	 */
	public function init_hooks() {

		add_filter( 'ainsys_webhook_action_handlers', [ $this, 'register_webhook_handler' ], 10, 1 );
	}


	public function register_webhook_handler( $handlers = [] ) {

		$handlers[ self::$entity ] = [ $this, 'handler' ];

		return $handlers;
	}


	public function handler( $action, $data, $object_id = 0 ) {

		$data = (array) $data;

		$response = __( 'Action not registered, Please implement actions for Comments', AINSYS_CONNECTOR_TEXTDOMAIN );

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


	/**
	 * @param  array $data
	 * @param        $action
	 *
	 * @return string
	 */
	protected function create( array $data, $action ): string {

		if ( Conditions::has_entity_disable_create( self::$entity, $action, 'incoming' ) ) {
			return sprintf( __( 'Error: %s creation is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity );
		}

		$error = __( 'Error: Failed to create a comment', AINSYS_CONNECTOR_TEXTDOMAIN );

		$comment_id = wp_insert_comment( wp_slash( $data ) );

		if ( is_wp_error( $comment_id ) ) {
			return $this->handle_error( $data, $comment_id, $error, self::$entity, $action );
		}

		$message = $this->message_success( $action, $comment_id );

		Logger::save(
			[
				'object_id'       => $comment_id,
				'entity'          => self::$entity,
				'request_action'  => $action,
				'request_type'    => 'incoming',
				'request_data'    => serialize( $data ),
				'server_response' => serialize( $message ),
			]
		);

		return $message;
	}


	protected function update( $data, $action ): string {

		if ( Conditions::has_entity_disable_update( self::$entity, $action, 'incoming' ) ) {
			return sprintf( __( 'Error: %s update is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity );
		}

		$error = __( 'Error: Perhaps such a comment does not exist', AINSYS_CONNECTOR_TEXTDOMAIN );

		$result = wp_update_comment( wp_slash( $data ), true );

		if ( is_wp_error( $result ) ) {
			return $this->handle_error( $data, $result, $error, self::$entity, $action );
		}

		$message = $this->message_success( $action, $result );

		Logger::save(
			[
				'object_id'       => $result,
				'entity'          => self::$entity,
				'request_action'  => $action,
				'request_type'    => 'incoming',
				'request_data'    => serialize( $data ),
				'server_response' => serialize( $message ),
			]
		);

		return $message;
	}


	protected function delete( $object_id, $data, $action ): string {

		if ( Conditions::has_entity_disable_delete( self::$entity, $action, 'incoming' ) ) {
			return sprintf( __( 'Error: %s delete is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity );
		}

		$error = __( 'Error: user is not deleted', AINSYS_CONNECTOR_TEXTDOMAIN );

		$result = wp_delete_comment( $object_id, true );

		if ( is_wp_error( $result ) ) {
			return $this->handle_error( $data, $result, $error, self::$entity, $action );
		}

		$message = $this->message_success( $action, $object_id );

		Logger::save(
			[
				'object_id'       => $result,
				'entity'          => self::$entity,
				'request_action'  => $action,
				'request_type'    => 'incoming',
				'request_data'    => serialize( $data ),
				'server_response' => serialize( $message ),
			]
		);

		return $message;
	}

}