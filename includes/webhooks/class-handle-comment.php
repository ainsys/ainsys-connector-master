<?php

namespace Ainsys\Connector\Master\Webhooks;

use Ainsys\Connector\Master\Conditions;
use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Webhook_Handler;

class Handle_Comment extends Handle implements Hooked, Webhook_Handler {

	protected static string $entity = 'comment';


	public function register_webhook_handler( $handlers = [] ) {

		$handlers[ self::$entity ] = [ $this, 'handler' ];

		return $handlers;
	}


	/**
	 * @param  array  $data
	 * @param  string $action
	 *
	 * @return array
	 */
	protected function create( array $data, string $action ): array {

		if ( Conditions::has_entity_disable( self::$entity, $action, 'incoming' ) ) {
			return [
				'id'      => 0,
				'message' => $this->handle_error(
					$data,
					'',
					sprintf( __( 'Error: %s creation is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity ),
					self::$entity,
					$action
				),
			];
		}

		$result = wp_insert_comment( wp_slash( $data ) );

		return [
			'id'      => $result ? : 0,
			'message' => $this->get_message( $result, $data, self::$entity, $action ),
		];
	}


	/**
	 * @param $data
	 * @param $action
	 * @param $object_id
	 *
	 * @return array
	 */
	protected function update( $data, $action, $object_id ): array {

		if ( Conditions::has_entity_disable( self::$entity, $action, 'incoming' ) ) {
			return [
				'id'      => 0,
				'message' => $this->handle_error(
					$data,
					'',
					sprintf( __( 'Error: %s update is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity ),
					self::$entity,
					$action
				),
			];
		}

		$result = wp_update_comment( wp_slash( $data ), true );

		return [
			'id'      => is_wp_error( $result ) ? 0 : $result,
			'message' => $this->get_message( $result, $data, self::$entity, $action ),
		];

	}


	/**
	 * @param $object_id
	 * @param $data
	 * @param $action
	 *
	 * @return array
	 */
	protected function delete( $object_id, $data, $action ): array {

		if ( Conditions::has_entity_disable( self::$entity, $action, 'incoming' ) ) {
			return [
				'id'      => 0,
				'message' => $this->handle_error(
					$data,
					'',
					sprintf( __( 'Error: %s delete is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity ),
					self::$entity,
					$action
				),
			];
		}

		$result = wp_delete_comment( $object_id, true );

		return [
			'id'      => $result ? $object_id : 0,
			'message' => $this->get_message( $result, $data, self::$entity, $action ),
		];
	}

}